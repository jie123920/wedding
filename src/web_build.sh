#!/bin/bash
gulp build
cp -rf ./web/Public/src/static ./web/Public/dist
cp -rf ./web/Public/src/wedding  ./web/Public/dist