{{/* vim: set filetype=mustache: */}}
{{/*
Expand the name of the chart.
*/}}
{{- define "gatekeeper-deployment.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "gatekeeper-deployment.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define "gatekeeper-deployment.labels" -}}
helm.sh/chart: {{ include "gatekeeper-deployment.chart" . }}
{{ include "gatekeeper-deployment.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "gatekeeper-deployment.selectorLabels" -}}
app.kubernetes.io/name: {{ include "gatekeeper-deployment.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{/*
Cert Manager Annotations
*/}}
{{- define "gatekeeper-deployment.cert-manager-annotations" -}}
cert-manager.io/cluster-issuer: {{ .Values.cert_manager.annotations.cluster_issuer }}
{{- end }}
