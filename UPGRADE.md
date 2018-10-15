# Upgrade

## RC4 to 1.0

+ The transaction config is not stored in the payment process anymore, it must be registered in the module instead!
+ Run the migrate command `./luya migrate`.
+ Change the payment module class in the config from `luya\payment\Module` to `luya\payment\frontend\Module`.
+ Register the amount trough `addItem()`, `addShipping()` and `addTax()` in order to ensure the totalAmount must be set as well.
+ Renamed `luya\payment\PaymentProcess` to `luya\payment\Pay`.
+ Included admin module as migrations are stored in the admin `['paymentadmin' => 'luya\payment\admin\Module']`.