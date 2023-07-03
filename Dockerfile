ARG MW_VERSION
ARG PHP_VERSION
ARG DB_TYPE

FROM gesinn/mediawiki:${MW_VERSION}-php${PHP_VERSION}
ENV EXTENSION=IDProvider

COPY composer*.json /var/www/html/extensions/$EXTENSION/

RUN cd extensions/$EXTENSION && \
    composer update

COPY . /var/www/html/extensions/$EXTENSION

# Create file containing PHP code to setup extension; to be appended to LocalSettings.php
RUN echo \
        "wfLoadExtension( '$EXTENSION' );\n" \
    >> __setup_extension__
