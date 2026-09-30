# The environment the UI screenshots are rendered in, on CI and locally (see compose.yml).
# Everything that changes pixels is pinned here: the Chromium build and the fonts.
FROM ubuntu:24.04

ARG PLAYWRIGHT_VERSION=1.63.0
ARG PHP_VERSION=8.1
ENV DEBIAN_FRONTEND=noninteractive \
    PLAYWRIGHT_BROWSERS_PATH=/ms-playwright \
    LANG=C.UTF-8 \
    TZ=UTC

# PHP from the ondrej PPA, the same builds setup-php uses on the GitHub runners
RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl git gnupg software-properties-common unzip \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        php${PHP_VERSION}-cli php${PHP_VERSION}-bcmath php${PHP_VERSION}-curl php${PHP_VERSION}-gd \
        php${PHP_VERSION}-mbstring php${PHP_VERSION}-mysql php${PHP_VERSION}-redis php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip mysql-client \
        fonts-dejavu-core fonts-liberation fonts-noto-color-emoji \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY --from=node:24-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && npx -y playwright@${PLAYWRIGHT_VERSION} install --with-deps chromium \
    && rm -rf /var/lib/apt/lists/* /root/.npm \
    # Chromium's fallback fixed font is "Courier New", which fontconfig maps to Liberation Mono, while
    # generic monospace is DejaVu Sans Mono: without Liberation Mono both end up the same
    && rm -f /usr/share/fonts/truetype/liberation*/LiberationMono-* \
    && fc-cache -f

COPY php.ini /etc/php/${PHP_VERSION}/cli/conf.d/99-matomo-tests.ini
COPY run.sh /usr/local/bin/matomo-ui-tests

WORKDIR /home/runner/work/matomo/matomo
ENTRYPOINT ["matomo-ui-tests"]
