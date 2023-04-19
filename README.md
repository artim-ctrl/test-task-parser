### Test Task 19.04.23

## How to start

```bash
docker compose up -d app mysql
```

# Connect to the app
```bash
docker compose exec app /bin/sh
```

# Migrate the database
```bash
php yii migrate --interactive=0
```

# Copy your file to the root directory of the project

# Import the file
```bash
php yii import <file-name.csv>
```
