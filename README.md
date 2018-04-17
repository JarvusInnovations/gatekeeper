# git-client

Promise-based git client that mostly just executes the git binary

## Usage

### Basic usage

```js
const git = require('git-client');
const hash = await git('rev-parse HEAD');
```

### Multiple arguments

```js
const hash = await git('rev-parse', 'HEAD');
```

### Option building

```js
const hash = await git('rev-parse', { verify: true, short: 6 }, 'HEAD');
```

### Named methods for common commands

```js
const hash = await git.revParse({ verify: true }, 'HEAD');
```

## Advanced Examples

### Save file from the web

```js
const writer = await git.hashObject({  w: true, stdin: true, $spawn: true });
const response = await axios.get('https://placekitten.com/1000/1000', { responseType: 'stream' });

// pipe data from HTTP response into git
response.data.pipe(writer.stdin);

// wait for data to finish
await new Promise((resolve, reject) => {
    response.data.on('end', () => resolve());
    response.data.on('error', () => reject());
});

// read written hash and save to ref
const hash = await writer.captureOutputTrimmed();
```

### Build a tree

```js
const lines = [
    '100644 blob bc0c330151d9a2ca8d87d1ff914b87f152036b19\tkitten.jpg',
    '100644 blob 97ab63ad46e50ac4012ac9370b33878b224c4fa3\tcage.jpg'
];

const mktree = await git.mktree({ $spawn: true });

const hash = await mktree.captureOutputTrimmed(lines.join('\n')+'\n');
```
