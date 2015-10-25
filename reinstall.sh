#!/usr/bin/env bash
drush si --site-name=Linkit8 --account-pass=admin -yvd --notify; drush en linkit devel config_inspector simpletest -y
