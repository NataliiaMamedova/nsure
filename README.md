# symfony-skeleton

Requirements:
- dev shared infra configured (https://gitlab.com/techcloud/shared/dev-infra)

- Create file `~/.composer/auth.json`
```json
{
  "gitlab-token": {
    "gitlab.com": "<Your access token from GitLab>"
  }
}
```
- Define new project name
```bash
export PROJECT_NAME=new_project_name
```

- Bootstrap new project
```bash
docker run --rm --interactive --tty \
-v $PWD:/app \
-v ${COMPOSER_HOME:-$HOME/.composer}:/tmp \
-u $(id -u):$(id -g) \
composer:2.1 \
composer create-project paybis/symfony-skeleton $PROJECT_NAME --repository=https://gitlab.com/api/v4/group/5811436/-/packages/composer/packages.json
```

