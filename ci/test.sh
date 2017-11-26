#!/bin/bash

set -ex

vendor/bin/phpcs

vendor/bin/phpunit

ci/cli-test.sh
