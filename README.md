
# Установка и использование

## Установить как cli-скрипт

```bash
git clone git@github.com:6umpukc/6umpukc.git ~/bin/6umpukc && cd ~/bin/6umpukc/bin && chmod +x bx && MSYS=winsymlinks:native ./bx install
```

## Список доступных команд

`bx help`

## Описание команды

`bx help [command]`

например:

`bx help install-php`

[Список команд с описаниями](COMMANDS.md)

## Запуск php, node и т.д. с предустановленым окружением

### PHP

`bx php [аргументы php]`

В .env указать

`SOLUTION_PHP_BIN=путь к интерпретатору php нужной версии`

### Node

`bx node [аргументы node]`

В .env указать

`SOLUTION_NODE_BIN=путь к node нужной версии`
