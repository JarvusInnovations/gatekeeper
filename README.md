# Jarvus GateKeeper

GateKeeper is a web application designed to sit between internal API endpoints and public users.
It provides a central facility for logging, analyzing, rate-limiting, and credentialing access.

## Features

- **Dashboard**: List of all mapped endpoints ordered by current traffic. Charts request count, response time, and cache hit ratio in real-time
- **Create and configure endpoint mappings**: Create new or configure existing public endpoints that map to internal API endpoints from the web GUI
- **Versioning**: Optionally maintain multiple versions for any endpoint
- **Email alerts**: Configure an administrative contact for each endpoint to receive email alerts when it returns a 5xx error or times out
- **Deprecation**: Set a deprecation date for an API and an appropriate HTTP status will be returned once that date is reached
- **Rewrites**: Regex-powered rewrites can be created and managed from the web GUI for any endpoint
- **Caching**: Duplicate requests are served from memory without hitting the internal API endpoint again if the original response included caching headers. Cached responses can be browsed by endpoint
- **Rate limiting**: Global and per-user rate limits configurable for each endpoint
- **Bandwidth limiting**: Global bandwidth limits configurable for each endpoint
- **Key management**: Configure endpoints to require keys, issue keys, and grant keys access to individual or all endpoints with an optional expiration date.
- **Ban management**: Ban a key or an IP permanently or until a given date
- **Logging**: Exportable logs record endpoint, path, response code, response time, response size, client IP, and key if provided for every transaction
- **Top users report**: View top users by IP or key over any given time period, globally or for a given endpoint
- **Uptime monitoring**: Ping your internal endpoints on a regular interval to test for a healthy response with an optional regex pattern for the response body

## Roadmap

These features are currently under development for the next release:

- Historical metrics
- Email subscriptions for the public to receive notices about specific endpoints
- Public portal endpoints' status, documentation, and test consoles
- Public portal for obtaining API keys
- Advanced filtering, sorting, and searching for transactions log

## Requirements

The GateKeeper application is built on the Emergence PHP framework and deployment engine, and requires an Emergence server to host it.

Emergence takes just a few minutes to setup on a Linux VM, and is designed to have a fresh system to itself. Once launched
it will configure services on the machine as-needed to host an instance of the application along with any other
sites, clones, or child sites. The guides for Ubuntu and Gentoo are most up-to-date: http://emr.ge/docs/setup

## Installation

### Launch instance

#### Option 1) fork via Emergence (recommended)

- Create an emergence site that extends http://Nrnt1W9ER1FewOP1@gatekeeper.sandbox01.jarv.us

This video walks through the complete process of installing emergence and then instantiating the GateKeeper application:

[![Walkthrough Video](http://b.vimeocdn.com/ts/455/313/455313620_640.jpg)](https://vimeo.com/79587819)

#### Option 2) clone from Git repository

- Create an emergence site that extends http://lKhjNhwXoM8rLbXw@skeleton-v2.emr.ge
- Upload contents of git repository using WebDAV client (CyberDuck is the best open-source option)

### Install heartbeat cron script

```bash
printf "*/5 *\t* * *\troot\temergence-fire-event gatekeeper-test heartbeat Gatekeeper > /dev/null\n" | sudo tee /etc/cron.d/gatekeeper-heartbeat
```
