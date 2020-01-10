# baps (Better Application System)
A wordpress plugin providing a customisable online application system.

## How to use
I've included a Docker Compose environment for testing and developing. In order to use it, you have to have [Docker](https://www.docker.com/) and [Docker Compose](https://docs.docker.com/compose/) installed on your machine. To start it, simply run docker-compose in the `docker_env` folder:
```
git clone https://github.com/CPUFronz/baps.git
cd baps/docker_env
docker-compose up
```
Once everything us up and running, you have to install Wordpress by opening [localhost](http://localhost) in your browser.


## Acknowledgment
Code for the Docker Compose environment based on [WPDC - WordPress Docker Compose](https://github.com/nezhar/wordpress-docker-compose) by [Harald Nezbeda](https://github.com/nezhar).
