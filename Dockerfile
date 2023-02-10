ARG MW_VERSION
FROM gesinn/docker-mediawiki-sqlite:${MW_VERSION}

RUN mkdir -p /data/sqlite && chown -R www-data. /data && \
    # To be able to persist configuration via docker volumes
    rm -f LocalSettings.php && ln -s /data/LocalSettings.php && \
    sed -i s/80/8080/g /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf 

ENV EXTENSION=IDProvider
COPY composer*.json /var/www/html/extensions/$EXTENSION/

RUN cd extensions/$EXTENSION && \
    composer update

COPY . /var/www/html/extensions/$EXTENSION

# Create file containing PHP code to setup extension; to be appended to LocalSettings.php
RUN echo \
        "wfLoadExtension( '$EXTENSION' );\n" \
    >> __setup_extension__
