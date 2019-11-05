# BUILD-USING:    docker build -t code-kombat/base-runner .
# RUN-USING:      docker run --rm --name=runner-base code-kombat/base-runner --help

# Pull base image.
FROM ubuntu:18.04

ENV DEBIAN_FRONTEND noninteractive

## install curl, git
RUN apt-get update && apt-get install -yq --no-install-recommends apt-utils\
    curl\
    git

## install php

RUN apt-get update && apt-get install -yq --no-install-recommends software-properties-common
RUN add-apt-repository ppa:ondrej/php
RUN apt-get update && apt-get install -yq --no-install-recommends php7.3\
    php7.3-cli \
    php7.3-json \
    php7.3-xml \
    php7.3-zip

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# create non root user
RUN groupadd -g 999 appuser && \
    useradd -r -u 999 -g appuser appuser
RUN mkdir /home/appuser
RUN chown -R appuser:appuser /home/appuser/

## install php libraries

USER appuser
WORKDIR /home/appuser
RUN composer require symfony/process
USER root
