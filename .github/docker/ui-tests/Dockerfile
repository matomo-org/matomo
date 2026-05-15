# syntax=docker/dockerfile:1.7
#
# Prebuilt base image for the matomo-tests UI-plugins and UI-core CI jobs.
#
# What's baked in:
#   - PHP 7.2 (from ondrej/php PPA) with the extension list used by
#     matomo-org/github-action-tests prepare.sh
#   - Node.js 16, Composer 2, nginx
#   - google-chrome-stable + matching chromedriver
#   - imagemagick (with ImageMagick policy tweaks already applied),
#     ghostscript, fonts (droid-fallback, noto-mono, urw-base35, xfonts),
#     ripgrep
#   - composer install result at /opt/matomo-prebuilt/vendor
#   - tests/lib/screenshot-testing npm ci result (puppeteer + its bundled
#     Chromium download) at /opt/matomo-prebuilt/tests/lib/screenshot-testing/node_modules
#
# The image's tag is derived from the hash of this Dockerfile plus the
# lockfiles whose state it bakes in. When a PR changes any of those files
# the image won't exist for that hash; the test workflow should fall back
# to the existing matomo-org/github-action-tests setup path.

FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=UTC \
    LANG=C.UTF-8 \
    LC_ALL=C.UTF-8 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_NO_AUDIT=1 \
    COMPOSER_PROCESS_TIMEOUT=0

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
        ca-certificates curl wget gnupg lsb-release \
        software-properties-common \
        git unzip xz-utils \
        sudo \
 && rm -rf /var/lib/apt/lists/*

# PHP 7.2 from ondrej/php
RUN LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php \
 && apt-get update \
 && apt-get install -y --no-install-recommends \
        php7.2 \
        php7.2-cli \
        php7.2-fpm \
        php7.2-bcmath \
        php7.2-curl \
        php7.2-gd \
        php7.2-intl \
        php7.2-ldap \
        php7.2-mbstring \
        php7.2-mysql \
        php7.2-opcache \
        php7.2-redis \
        php7.2-xml \
        php7.2-zip \
 && rm -rf /var/lib/apt/lists/* \
 && update-alternatives --set php /usr/bin/php7.2

# Composer 2.x (pinned to a recent 2.x at build time)
RUN curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php \
 && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer --2 \
 && rm /tmp/composer-setup.php \
 && composer --version

# Node.js 16
RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
 && apt-get install -y --no-install-recommends nodejs \
 && rm -rf /var/lib/apt/lists/* \
 && node --version && npm --version

# Google Chrome (stable) + ChromeDriver
RUN curl -fsSL https://dl.google.com/linux/linux_signing_key.pub \
        | gpg --dearmor -o /usr/share/keyrings/google-chrome.gpg \
 && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/google-chrome.gpg] https://dl.google.com/linux/chrome-stable/deb stable main" \
        > /etc/apt/sources.list.d/google-chrome.list \
 && apt-get update \
 && apt-get install -y --no-install-recommends google-chrome-stable \
 && rm -rf /var/lib/apt/lists/* \
 && google-chrome --version

RUN set -eux; \
    CHROME_VERSION="$(google-chrome --version | awk '{print $3}')"; \
    curl -fsSL "https://storage.googleapis.com/chrome-for-testing-public/${CHROME_VERSION}/linux64/chromedriver-linux64.zip" \
        -o /tmp/chromedriver.zip; \
    unzip -q /tmp/chromedriver.zip -d /usr/local/share/; \
    rm /tmp/chromedriver.zip; \
    ln -sf /usr/local/share/chromedriver-linux64/chromedriver /usr/local/bin/chromedriver; \
    chromedriver --version
ENV CHROMEWEBDRIVER=/usr/local/share/chromedriver-linux64 \
    CHROME_BIN=/usr/bin/google-chrome

# Test environment system packages
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
        imagemagick \
        ghostscript \
        fonts-droid-fallback \
        fonts-noto-mono \
        fonts-urw-base35 \
        xfonts-encodings \
        xfonts-utils \
        libgs10 libgs10-common libgs-common \
        libidn12 \
        libijs-0.35 \
        libjbig2dec0 \
        libnetpbm11t64 \
        libpaper1 libpaper-utils \
        netpbm \
        poppler-data \
        nginx \
        ripgrep \
 && rm -rf /var/lib/apt/lists/*

# ImageMagick policy tweaks (matches matomo-org/github-action-tests prepare.sh)
RUN sed -i -E 's/name="memory" value="[^"]+"/name="memory" value="2GiB"/g'   /etc/ImageMagick-6/policy.xml \
 && sed -i -E 's/name="width" value="[^"]+"/name="width" value="64KP"/g'    /etc/ImageMagick-6/policy.xml \
 && sed -i -E 's/name="height" value="[^"]+"/name="height" value="64KP"/g'  /etc/ImageMagick-6/policy.xml \
 && sed -i -E 's/name="area" value="[^"]+"/name="area" value="1GiB"/g'      /etc/ImageMagick-6/policy.xml \
 && sed -i -E 's/name="disk" value="[^"]+"/name="area" value="4GiB"/g'      /etc/ImageMagick-6/policy.xml

# Pre-baked dependencies at /opt/matomo-prebuilt.
# Consumers should copy or symlink these into the workspace when the
# lockfile hash matches the image tag.
WORKDIR /opt/matomo-prebuilt

COPY composer.json composer.lock ./
RUN composer config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer false \
 && composer install --ignore-platform-reqs --no-progress

COPY tests/lib/screenshot-testing/package.json tests/lib/screenshot-testing/package-lock.json \
        /opt/matomo-prebuilt/tests/lib/screenshot-testing/
WORKDIR /opt/matomo-prebuilt/tests/lib/screenshot-testing
RUN npm ci --no-audit --no-progress

WORKDIR /opt/matomo-prebuilt
