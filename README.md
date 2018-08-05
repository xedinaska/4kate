# Project Title

Этот репозиторий поможет установить приложение при помощи комбинации: PHP7 + Apache + MySQL

## Getting Started

- Нужно установить необходимые для работы git библиотеки: 
```
sudo apt-get install libcurl4-gnutls-dev libexpat1-dev gettext \
  libz-dev libssl-dev
```

- Затем установить git: 
```
sudo apt-get install git
```

### Prerequisites

После установки git нужно скачать этот проект используя следующую команду: 

```
git clone https://github.com/xedinaska/4kate
```

### Installing

- Нужно установить Docker 

```
sudo apt-get update
sudo apt-get install docker-ce
```

- Затем установить docker-compose: https://docs.docker.com/compose/install/

- Затем выполнить скрипт run.sh: 
```
sudo chmod +x run.sh
./run.sh 
```

Если все прошло успешно -  PhpMyAdmin будет доступен тут: 
http://localhost:8080 (root/rootpwd)

После импорта базы данных testdb приложение будет доступно тут: http://localhost


## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
