# How to contribute

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
<!-- generated with [DocToc](https://github.com/thlorenz/doctoc)* -->

- [How to contribute to Matomo core?](#how-to-contribute-to-matomo-core)
- [How to submit a bug report or suggest a feature?](#how-to-submit-a-bug-report-or-suggest-a-feature)
- [How to suggest improvements to translations?](#how-to-suggest-improvements-to-translations)
- [How to submit code improvements via pull requests?](#how-to-submit-code-improvements-via-pull-requests)
- [Docker-based development environment](#docker-based-development-environment)
  - [Capabilities](#capabilities)
  - [Prerequisites](#prerequisites)
  - [Matomo admin user credentials](#matomo-admin-user-credentials)
  - [Managing the virtual environment](#managing-the-virtual-environment)
    - [Running](#running)
    - [Stopping](#stopping)
    - [Deleting](#deleting)
  - [How to run some CLI in Matomo container](#how-to-run-some-cli-in-matomo-container)
  - [How to update the Docker development environment to a new version of Matomo](#how-to-update-the-docker-development-environment-to-a-new-version-of-matomo)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->


<a name="how-to-contribute-to-matomo-core"></a>
## How to contribute to Matomo core?

Great to have you here! Read the following guide on our developer zone to learn how you can help make this project better!

https://developer.matomo.org/guides/contributing-to-piwik-core

<a name="how-to-submit-a-bug-report-or-suggest-a-feature"></a>
## How to submit a bug report or suggest a feature?
Please read the recommendations on writing a good [bug report](https://developer.matomo.org/guides/core-team-workflow#submitting-a-bug-report) or [feature request](https://developer.matomo.org/guides/core-team-workflow#submitting-a-feature-request).

<a name="how-to-suggest-improvements-to-translations"></a>
## How to suggest improvements to translations?

You can help improve translations in Matomo, please read [contribute to translations](https://github.com/matomo-org/piwik/blob/master/lang/README.md).

<a name="how-to-submit-code-improvements-via-pull-requests"></a>
## How to submit code improvements via pull requests?

You can help contribute to Matomo codebase via Pull Requests, see [Contributing to Matomo core](https://developer.matomo.org/guides/contributing-to-piwik-core)


<a name="docker-based-development-environment"></a>
## Docker-based development environment

<a name="capabilities"></a>
### Capabilities

Using the Docker-based environment you can:

- run Matomo with the source code at your working copy
- change source files and see the result on http://localhost
- debug PHP code with XDebug
- work with Matomo database

<a name="prerequisites"></a>
### Prerequisites

- The following ports must be free on your host:
    - `80`
    - `3306`
- Prepare the docker environment. Install the following:
    - [Docker](https://docs.docker.com/install/),
    - [Docker Compose](https://docs.docker.com/compose/install/)
- Setup Matomo config file
    ```bash
    cp misc/docker/config.ini.php config/
    ```

<a name="matomo-admin-user-credentials"></a>
### Matomo admin user credentials

login: admin
password: matomo

<a name="managing-the-virtual-environment"></a>
### Managing the virtual environment

<a name="running"></a>
#### Running

```bash
docker-compose up
```


<a name="stopping"></a>
#### Stopping

```bash
docker-compose stop
```


<a name="deleting"></a>
#### Deleting

```bash
docker-compose down -v
```


### How to run some CLI in Matomo container
```bash
docker-compose exec matomo ./console
```
XDebug will try to connect from the container to your host.


<a name="how-to-update-the-docker-development-environment-to-a-new-version-of-matomo"></a>
### How to update the Docker development environment to a new version of Matomo

1. Run the environment
    ```bash
    docker-compose up
    ```

2. Log in to matomo

3. Update Matomo via GUI

4. Dump the database
    ```bash
    docker-compose exec -T db mysqldump -uroot -pmatomo matomo > misc/docker/db.sql
    ```

5. Edit the database dump
	1. 'install_version','3.8.1' -> 'install_version','3.9.1'
	2. remove the sessions

6. Copy the Matomo config
    ```bash
    cp config/config.ini.php misc/docker/config.ini.php
    ```

7. Delete the environment
    ```bash
    docker-compose down -v
    ```

8. Rename Dockerfile with new version
    ```bash
    mv misc/docker/Dockerfile-matomo-3.8.1-apache-dev misc/docker/Dockerfile-matomo-3.9.1-apache-dev
    ```

9. Change Matomo version at `docker-compose.yml` and `misc/docker/Dockerfile-matomo-x.x.x-apache-dev`

10. Run and check the environment
    ```bash
    docker-compose up
    ```

11. Commit the changes
    ```bash
    git commit -m 'Docker development environment was updated up to 3.9.1'
    ```