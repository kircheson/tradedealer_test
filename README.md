## Тестовое задание на разработку REST API для автосалона

Функционал:

• Получать информацию об автомобилях

• Рассчитывать параметры кредита

• Сохранять кредитные заявки

Cтек: PHP 8+, Symfony, MySQL, Docker



## Инструкции по настройке проекта

1. Клонируйте репозиторий:
**git clone https://github.com/your-username/tradedealer.git**
**cd tradedealer**

2. Запустите Docker-контейнеры:
**docker compose up -d**

3. Получите доступ к PHP-контейнеру:
**docker exec -it tradedealer-php bash**

4. Установите зависимости PHP:
**composer install**

5. Выполните миграции базы данных:
**bin/console doctrine:migrations:migrate**

6. Загрузите фикстуры (тестовые данные):
**bin/console doctrine:fixtures:load**


## PS
Коллекцией тестов:
**Postman TESTS API endpoints**

Исходник ТЗ тестового задания:
**https://cloud.mail.ru/public/pU1N/LcF96CDwA**
