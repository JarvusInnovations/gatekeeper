# Jarvus GateKeeper

GateKeeper is a web application designed to sit between internal API endpoints and public users.
It provides a central facility for logging, analyzing, rate-limiting, and credentialing access.

## Requirements
The GateKeeper application is built on the Emergence PHP framework and deployement engine, and requires an Emergence server to host it.

Emergence takes just a few minutes to setup on a Linux VM, and is designed to have a fresh system to itself. Once launched
it will configure services on the machine as-needed to host an instance of the application along with any other
sites, clones, or child sites. The guides for Ubuntu and Gentoo are most up-to-date: http://emr.ge/docs/setup

## Installation via Emergence (linked child)
-  Create an emergence site that extends http://Nrnt1W9ER1FewOP1@gatekeeper.sandbox01.jarv.us

This video walks through the complete process of installing emergence and then instantiating the GateKeeper application:
[![Walkthrough Video](http://b.vimeocdn.com/ts/455/313/455313620_640.jpg)](https://vimeo.com/79587819)

## Installation from Git
-  Create an emergence site that extends http://Mw7U1bUeVZJbACka@ryon-sandbox.sites.emr.ge
-  Upload contents of git repository using WebDAV client (CyberDuck is the best open-source option)
