#!/bin/bash

set -ex

vendor/bin/phpcs

vendor/bin/phpunit
