## IPS Module reminder Assigner

As per the Problem statement, the logic has been written to attach the required tags to modules for the customer on infusion soft api, to remind them of the next module to start with. Please run the below steps in sequential order after cloning this repo :

- Make sure a valid env file is created after installation.
```php
php artisan migrate

php artisan migrate --path=database/migrations/tagsTable

php artisan db:seed
```
- create multiple users with dummy data to test the api by calling the route multiple times ```/api/create_test_contact```

- The API endpoint is ```/api/module_reminder_assigner``` which is a POST request and accepts ```contact_email``` parameter for a valid infusionSoft contact.
