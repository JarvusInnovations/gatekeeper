const semver = require('semver');
const fs = require('mz/fs');
const child_process = require('child_process');

const logger = (() => {
    const emptyFn = function () {};

    return (require.main && require.main.exports.logger) || {
        log: emptyFn,

        error: emptyFn,
        warn: emptyFn,
        info: emptyFn,
        verbose: emptyFn,
        debug: emptyFn,
        silly: emptyFn
    };
})();

/**
 * TODO:
 * - [ ] Port cliOptionsToArgs
 * - [ ] Add support for gitOptions
 */

/**
 * Represents and provides an interface to an executable git binary
 * available in the host environment
 */
class Git {
    constructor ({ command = null, gitDir = null, workTree = null } = {}) {
        this.command = command || this.command;
        this.gitDir = gitDir;
        this.workTree = workTree;

        this.version = null;
    }

    /**
     * @static
     * Gets complete path to git directory
     */
    static async getGitDirFromEnvironment() {
        const gitDir = await Git.prototype.exec('rev-parse', { 'git-dir': true });

        return await fs.realpath(gitDir);
    }


    /**
     * @static
     * Gets complete path to working tree
     */
    static async getWorkTreeFromEnvironment() {
        const workTree = await Git.prototype.exec('rev-parse', { 'show-toplevel': true });

        return await workTree ? fs.realpath(workTree) : Promise.resolve(null);
    };

    /**
     * Get the version of the hab binary
     * @returns {?string} Version reported by habitat binary, or null if not available
     */
    async getVersion () {
        if (this.version === null) {
            try {
                const output = await this.exec({ version: true });
                [, this.version] = /^git version (\d+\.\d+\.\d+)/.exec(output);
            } catch (err) {
                this.version = false;
            }
        }

        return this.version || null;
    }

    /**
     * Check if git version is satisfied
     * @param {string} range - The version or range habitat should satisfy (see https://github.com/npm/node-semver#ranges)
     * @returns {boolean} True if habitat version satisfies provided range
     */
    async satisfiesVersion (range) {
        return semver.satisfies(await this.getVersion(), range);
    }

    /**
     * Ensure that git version is satisfied
     * @param {string} range - The version or range habitat should satisfy (see https://github.com/npm/node-semver#ranges)
     * @returns {Git} Returns current instance or throws exception if version range isn't satisfied
     */
    async requireVersion (range) {
        if (!await this.satisfiesVersion(range)) {
            throw new Error(`Git version must be ${range}, reported version is ${await this.getVersion()}`);
        }

        return this;
    }


    /**
     * Executes git with given arguments
     * @param {string|string[]} args - Arguments to execute
     * @param {?Object} execOptions - Extra execution options
     * @returns {Promise}
     */
    async exec (...args) {
        let command;
        const commandArgs = [];
        const commandEnv = {};
        const execOptions = {
            gitDir: this.gitDir,
            workTree: this.workTree
        };

        logger.debug('building command', args);


        // scan through all arguments
        let arg;

        while (arg = args.shift()) {
            switch (typeof arg) {
                case 'string':
                    if (!command) {
                        command = arg; // the first string is the command
                        break;
                    }
                    // fall through and get pushed with numbers
                case 'number':
                    commandArgs.push(arg.toString());
                    break;
                case 'object':

                    // extract any git options
                    if ('$gitDir' in arg) {
                        execOptions.gitDir = arg.$gitDir;
                        delete arg.$gitDir;
                    }

                    if ('$workTree' in arg) {
                        execOptions.workTree = arg.$workTree;
                        delete arg.$workTree;
                    }

                    if ('$indexFile' in args) {
                        gitEnv.GIT_INDEX_FILE = args.$indexFile;
                        delete args.$indexFile;
                    }


                    // extract any general execution options
                    if ('$nullOnError' in arg) {
                        execOptions.nullOnError = arg.$nullOnError;
                        delete arg.$nullOnError;
                    }

                    if ('$spawn' in arg) {
                        execOptions.spawn = arg.$spawn;
                        delete arg.$spawn;
                    }

                    if ('$shell' in arg) {
                        execOptions.shell = arg.$shell;
                        delete arg.$shell;
                    }

                    if ('$env' in arg) {
                        for (let key in arg.$env) {
                            commandEnv[key] = arg.$options[key];
                        }
                        delete arg.$env;
                    }

                    if ('$preserveEnv' in arg) {
                        execOptions.preserveEnv = arg.$preserveEnv;
                        delete arg.$preserveEnv;
                    }

                    if ('$options' in arg) {
                        for (let key in arg.$options) {
                            execOptions[key] = arg.$options[key];
                        }
                    }

                    if ('$passthrough' in arg) {
                        if (execOptions.passthrough = Boolean(arg.$passthrough)) {
                            execOptions.spawn = true;
                        }
                        delete arg.$passthrough;
                    }

                    if ('$wait' in arg) {
                        execOptions.wait = Boolean(arg.$wait);
                        delete arg.$wait;
                    }


                    // any remaiing elements are args/options
                    commandArgs.push.apply(commandArgs, Array.isArray(arg) ? arg : cliOptionsToArgs(arg));
                    break;
                default:
                    throw 'unhandled exec argument';
            }
        }


        // prefixs args with command
        if (command) {
            commandArgs.unshift(command);
        }



        // prefix args with git-level options
        const gitOptions = {};

        if (execOptions.gitDir) {
            gitOptions['git-dir'] = execOptions.gitDir;
        }

        if (execOptions.workTree) {
            gitOptions['work-tree'] = execOptions.workTree;
        }

        commandArgs.unshift.apply(commandArgs, cliOptionsToArgs(gitOptions));


        // prepare environment
        if (execOptions.preserveEnv !== false) {
            Object.setPrototypeOf(commandEnv, process.env);
        }

        execOptions.env = commandEnv;



        // execute git command
        logger.debug(this.command, commandArgs.join(' '));

        if (execOptions.spawn) {
            const process = child_process.spawn(this.command, commandArgs, execOptions);

            if (execOptions.passthrough) {
                process.stdout.on('data', data => data.toString().trim().split(/\n/).forEach(line => logger.info(line)));
                process.stderr.on('data', data => data.toString().trim().split(/\n/).forEach(line => logger.error(line)));
            }

            if (execOptions.wait) {
                return new Promise((resolve, reject) => {
                    process.on('exit', code => {
                        if (code == 0) {
                            resolve();
                        } else {
                            reject(code);
                        }
                    });
                });
            }

            let capturePromise;
            process.captureOutput = () => {
                if (!capturePromise) {
                    capturePromise = new Promise((resolve, reject) => {
                        let output = '';

                        process.stdout.on('data', data => {
                            output += data;
                        });

                        process.on('exit', code => {
                            if (code == 0) {
                                resolve(output);
                            } else {
                                reject({ output, code });
                            }
                        });
                    });
                }

                return capturePromise;
            };

            return process;
        } else if (execOptions.shell) {
            return new Promise((resolve, reject) => {
                child_process.exec(`${this.command} ${commandArgs.join(' ')}`, execOptions, (error, stdout, stderr) => {
                    if (error) {
                        if (execOptions.nullOnError) {
                            return resolve(null);
                        } else {
                            error.stderr = stderr;
                            return reject(error);
                        }
                    }

                    resolve(stdout.trim());
                });
            });
        } else {
            return new Promise((resolve, reject) => {
                child_process.execFile(this.command, commandArgs, execOptions, (error, stdout, stderr) => {
                    if (error) {
                        if (execOptions.nullOnError) {
                            return resolve(null);
                        } else {
                            error.stderr = stderr;
                            return reject(error);
                        }
                    }

                    resolve(stdout.trim());
                });
            });
        }
    }
}


// set default git command
Git.prototype.command = 'git';


// add first-class methods for common git subcommands
[
    'add',
    'bisect',
    'branch',
    'cat-file',
    'checkout',
    'clean',
    'clone',
    'commit',
    'config',
    'diff',
    'fetch',
    'grep',
    'hash-object',
    'init',
    'log',
    'merge',
    'mv',
    'pull',
    'push',
    'rebase',
    'reflog',
    'remote',
    'reset',
    'rm',
    'show',
    'stash',
    'status',
    'submodule',
    'tag',
].forEach(command => {
    const method = command.replace(/-([a-zA-Z])/, (match, letter) => letter.toUpperCase());

    Git.prototype[method] = function(...args) {
        args.unshift(command);
        return git.exec.apply(git, args);
    }
});


// create a default instance
const git = new Git();


// expose exec function for default instance as export
module.exports = function () {
    return git.exec.apply(git, arguments);
};


// expose default instance as prototype of exported exec function
Object.setPrototypeOf(module.exports, git);


// expose class prototype
module.exports.Git = Git;


// private utilities
/**
 * @private
 * Convert an options object into CLI arguments string
 */
function cliOptionsToArgs(options) {
    var args = [],
        k, val;

    for (k in options) {
        if (k[0] == '_') {
            continue;
        }

        val = options[k];

        if (k.length == 1) {
            if (val === true) {
                args.push('-'+k);
            } else if (val !== false) {
                args.push('-'+k, val);
            }
        } else {
            if (val === true) {
                args.push('--'+k);
            } else if (val !== false) {
                args.push('--'+k+'='+val);
            }
        }
    }

    return args;
}