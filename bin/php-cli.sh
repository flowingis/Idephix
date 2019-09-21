#!/usr/bin/env bash

docker run -it --rm --name idephix-build \
  -v "$PWD":/usr/src/myapp -w /usr/src/myapp \
  idephix-build "$@"
