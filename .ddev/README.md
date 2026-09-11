# Matomo DDEV environment

**WARNING**: Matomo DDEV environment can be helpful when developing for Matomo, to be able to create or test new plugins locally, to run unit, integration, system or UI tests, but it is **highly discouraged** to use DDEV environments to run or host publicly accessible production installations of Matomo.

## Prerequisites

Before you begin, make sure you have DDEV installed. If you don't have it yet, follow the installation instructions in the official [DDEV documentation](https://ddev.readthedocs.io/en/stable/).

If you don't have a Docker provider already available, you will need to start by choosing one. We recommend installing [Rancher Desktop](https://rancherdesktop.io/), as it is free and open source.

By default, Rancher Desktop will use ports 80 and 443 for Traefik router for Kubernetes. To make these ports available to DDEV, we recommend disabling Traefik by unchecking the checkbox: Preferences > Kubernetes > Enable Traefik.

## Setup steps

### 1. Start the environment

Once DDEV is installed, navigate to your project directory and run:

```
ddev start
```

This command will start the DDEV environment for local development. By default, your local environment will be accessible at [https://matomo.ddev.site](https://matomo.ddev.site).

You can also open this URL directly in your browser by running:

```
ddev launch
```

This will automatically open the default browser and take you to the Matomo setup page.

### 2. Set up Matomo using the UI installer

Follow the on-screen instructions in the UI installer to complete the Matomo setup. This will configure the necessary database and settings for your local instance.

### 3. Set up the development environment

After Matomo is set up, you can initialize the development environment by running:

```
ddev matomo:init:dev
```

This command will set up the environment for development, installing the additional dependencies required.

We have also added a parameter to the `ddev matomo:init:dev` command to enable source maps for Vue components. 
To enable them, you can run:
```
ddev matomo:init:dev --with-sourcemaps
```

You can also disable sourcemaps at any time if you want to, just run the default command again:
```
ddev matomo:init:dev
```
NOTE: You should tick 'Disable cache' on your browser developer tools to see the changes.

To see help:
```
ddev help matomo:init:dev
```

### 4. Set up the testing environment

After Matomo is set up, you can initialize the testing environment by running:

```
ddev matomo:init:tests
```

This command will set up the environment for running tests, ensuring everything is in place for the UI and other automated tests.

## Usage

The command `ddev matomo:console` provides access to all Matomo console commands. Some useful commands include:

- `core:archive` – Run local archiving
- `generate:plugin` - Generate a new plugin/theme including all needed files
- `tests:run` – Run unit, integration, and system tests
- `tests:run-ui` – Run UI tests
- `tests:run-js` – Run tracker JavaScript tests
- `tests:run-vue` – Run Vue component unit tests
- `cache:clear` - Remove all caches, including CSS and JavaScript
- `vue:build` - Builds vue modules for one or more plugins

To mount local plugin repositories into Matomo without symlinks, use the host commands:

```
ddev matomo:plugins:mount
ddev matomo:plugins:mount ../plugin-FormAnalytics ../plugin-HeatmapSessionRecording
ddev matomo:plugins:mount ../ '^.*/plugin-(FormAnalytics|Funnels)$'
ddev matomo:plugins:unmount FormAnalytics
ddev matomo:plugins:unmount
```

`ddev matomo:plugins:mount` creates a local-only `.ddev/docker-compose.local-plugins.yaml`, adds bind mounts for discovered `plugin-*` directories, and restarts DDEV automatically. If you pass a plugin directory path directly, the command reads the plugin name from `plugin.json` when present and otherwise infers it from directory names like `plugin-FormAnalytics`.

When a plugin declares `require.php` in `plugin.json`, the mount command compares it with the PHP version currently used by DDEV. Simple constraints such as `>=7.2.0` are enforced and incompatible plugins are skipped. More complex Composer-style expressions are only warned about and the plugin is mounted anyway.

`ddev matomo:plugins:unmount` removes one or more managed plugin mounts by plugin name. If all managed mounts are removed, the generated compose override is deleted automatically.

For more information about Matomo development, check out the official [Matomo Developer Documentation](https://developer.matomo.org/).

## Syncing test files from CI

When a build produces different UI screenshots or system test files than your checkout expects, you can copy the files CI generated back into your working copy:

```
ddev matomo:artifacts:sync-screenshots 12345 'Marketplace_.*'
ddev matomo:artifacts:sync-system-tests 12345 -e
```

The first argument is the build number, and both commands accept `-r` to sync a plugin repository, eg `-r innocraft/plugin-FormAnalytics`.

Syncing from `matomo-org/matomo` needs no setup, because core's own artifacts are public. Every other repository is protected, plugin repositories under `matomo-org` included, so the commands look up credentials whenever you point them somewhere else with `-r`. The credentials come from git, so they are stored wherever your credential helper keeps them and never appear in a file in the repository, in the process list or in your shell history — the password is handed to the console over STDIN.

Store them once per machine:

```
ddev matomo:artifacts:login
```

Which credential helper git uses is your own configuration, exactly as it is for `git push`. Pick one that keeps the password in your operating system's credential store rather than in a file:

```
git config --global credential.helper osxkeychain   # macOS
git config --global credential.helper manager       # Windows
git config --global credential.helper libsecret     # Linux
```

Git ships the helpers for macOS and Windows, so nothing needs installing there. On Debian and Ubuntu the libsecret helper ships as source in `/usr/share/doc/git/contrib/credential/libsecret` and has to be built once. Inside WSL2, point git at the Windows credential manager instead. Note that `git config --global credential.helper store` writes `~/.git-credentials` in plain text, so it is a poor choice for this.

Without a helper configured, git accepts the credentials and silently stores nothing, so `ddev matomo:artifacts:login` reads them back afterwards and tells you if that happened rather than reporting a success you did not get.

These host commands are covered by `tests/shell/ddev-artifacts-commands.sh`, which needs neither DDEV nor credentials to run and is run by CI on Linux, macOS and Windows.

The console commands behind these wrappers, `tests:sync-ui-screenshots` and `development:sync-system-test-processed`, can also be run through `ddev matomo:console` directly. They take the password as `--http-password`, or read it from STDIN with `--http-password-stdin`.

## Update PHP or MySQL version

You can create `.ddev/config.local.yaml` to adjust the environment configuration. This file will be automatically ignored by git.

For example, you can adjust `php_version` to `8.4`. All configuration from `.ddev/config.yaml` can be overridden. Check [DDEV documentation](https://docs.ddev.com/en/stable/users/configuration/config/#managing-configuration) for more details.

From your host, run `ddev restart` for the changes to take effect.

## Generating testing data locally

To see some visits within your local Matomo instance, you don't need a website with the JavaScript tracking code installed. Instead, you can generate sample visits using the VisitorGenerator plugin.

Start by creating a site in the Matomo Dashboard. Then, run the following commands on your host machine:

```
ddev matomo:console development:enable
ddev matomo:console plugin:activate VisitorGenerator
ddev matomo:console visitorgenerator:generate-visits
```

## Known issues

Currently, the screenshots generated by UI tests do not match those generated in the CI environment. However, this does not affect the functionality of the tests themselves. The screenshots should still be good enough to verify that your changes or plugin are working correctly.
