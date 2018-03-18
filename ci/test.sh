#!/bin/bash

set -ex

vendor/bin/parallel-lint --exclude vendor .

vendor/bin/phpcs

vendor/bin/phpunit

ci/cli-test.sh
