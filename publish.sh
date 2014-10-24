#!/bin/bash

aws s3 sync _site s3://www.alltheducks.com --delete
