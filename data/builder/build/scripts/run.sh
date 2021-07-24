#!/usr/bin/env bash

# Ensure that the builder can install deps in mounted src root.
chown jekyll:jekyll /build/src

jekyll build --destination /dist --watch
