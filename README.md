## Getting Started

1. Create the .env file

```bash
cp .env.example .env
```

2. On the docker-compose.yml, change the laravel container volume to your app directory

```bash
    laravel:
      container_name: ficker_laravel
      image: gbzzz/laravel-php8.1
      ports:
        - 8080:80
      networks:
        - ficker
      volumes:
        - {Your app directory}:/app
      environment:
        - APP_ENV=local
```

3. Run the containers

```bash
docker compose up -d
```
