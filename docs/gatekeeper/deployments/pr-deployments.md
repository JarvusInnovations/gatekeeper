# PR Deployments:

## Github Secrets:

- `kube_namespace` (required)
Set this to the kubernetes namespace you wish for the app to be deployed to.

- `kube_config` (required)
Set this to the value **base64 encoded** kubeconfig file used to configure access to the cluster.

- `kube_hostname` (required)
Set this to the hostname of the kubernetes cluster.
*Hint:* If the `kube_hostname` is set to example.com, the app will be deployed to `pr-1.example.com`

## Kubernetes Secrets

- `regcred` (required)
Set this in the kubernetes cluster, within the `kube_namespace` to the login credentials for docker.


## Helm Configuration

#### Helm Template

Located at `k8s/charts/deployment/`

- `templates/` - Helm templates for PR Deployments
- `values.yaml` - Default configurable values for PR deployments

