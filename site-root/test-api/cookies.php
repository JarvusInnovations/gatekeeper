<?php

Gatekeeper\Gatekeeper::authorizeTestApiAccess();

JSON::respond($_COOKIE);