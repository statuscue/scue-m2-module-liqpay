# LiqPay for Magento 2 

LiqPay payment integration extension for Magento 2. 

# How it works

Integration works via the Payment Provider Gateway with some exceptions. In our case payment provider requires to redirecting client to a payment page. Magento's Payment Provider Gateway designed, in my opinion, to utilize
for server-server implementation. That's why I use only `initialize` command and implement neither
`authorize` nor `capture` ones. This command requires to initialize an order with "Pending Payment" status before placement.

According to the LiqPay's documentation we need to submit a form with a POST method and required fields `data` and `signature` to a payment page.

So, in our .js component `redirect-form.js` that renderer a payment method we make a call to a custom contorller `liqpay/checkout/start` after the customer clicked the Place Order button. In the custom controller `Controller/Checkout/Start.php` we get all the form data that we need to POST a form using the LiqPay SDK.

On success, we call the function from another custom component `form-builder.js` and submit the customer to a payment provider's URL.

The module can handle the callbacks by `/V1/liqpay/checkout/callback` REST route from LiqPay to update the order status and payment status. In response, we receive the `data` and` signature` fields. After verifying the `signature`, an invoice and a payment transaction will be created programmatically based on the `order_id` field from `data`. See `Model/Rest/Checkout/Callback.php` for implementation details.

The example of `data`:

```
Array
(
    [payment_id] => 1631528895
    [action] => pay
    [status] => success
    [version] => 3
    [type] => buy
    [paytype] => card
    [public_key] => sandbox_i1420262809
    [acq_id] => 414963
    [order_id] => 2000000068
    [liqpay_order_id] => 0PZT1Q6J1619521463347768
    [description] => LiqPay for Magento 2
    [sender_card_mask2] => 424242*42
    [sender_card_bank] => Test
    [sender_card_type] => visa
    [sender_card_country] => 804
    [ip] => 81.25.227.34
    [amount] => 50
    [currency] => UAH
    [sender_commission] => 0
    [receiver_commission] => 1.1
    [agent_commission] => 0
    [amount_debit] => 50
    [amount_credit] => 50
    [commission_debit] => 0
    [commission_credit] => 1.1
    [currency_debit] => UAH
    [currency_credit] => UAH
    [sender_bonus] => 0
    [amount_bonus] => 0
    [mpi_eci] => 7
    [is_3ds] => 
    [language] => ru
    [create_date] => 1619521463350
    [end_date] => 1619521463435
    [transaction_id] => 1631528895
)
```

# Links

- [Payment Provider Gateway](https://devdocs.magento.com/guides/v2.4/payments-integrations/bk-payments-integrations.html)
- [LiqPay Checkout](https://www.liqpay.ua/documentation/en/api/aquiring/checkout/doc)
- [LiqPay Callback](https://www.liqpay.ua/documentation/en/api/callback)
- https://devdocs.magento.com/guides/v2.4/howdoi/checkout/checkout_payment.html
- https://magento.stackexchange.com/a/158046
- https://magento.stackexchange.com/a/209921
- https://magento.stackexchange.com/a/211198
