<?php
// Redirect to the home page if id parameter not found in URL
if (empty($_GET['id'])) {
    header("Location: index.php");
}

// Include and initialize database class
include 'DB.class.php';
$db = new DB;

// Include and initialize paypal class
include 'PaypalExpress.class.php';
$paypal = new PaypalExpress;

// Get product ID from URL
$productID = $_GET['id'];

// Get product details
$conditions = array(
    'where' => array('id' => $productID),
    'return_type' => 'single'
);
$productData = $db->getRows('products', $conditions);

// Redirect to the home page if product not found
if (empty($productData)) {
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="https://www.paypalobjects.com/api/checkout.js"></script>
</head>

<body>
    <div class="item">
        <!-- Product details -->
        <img height="50" width="100" src="<?php echo $productData['image']; ?>" />
        <p>Name: <?php echo $productData['name']; ?></p>
        <p>Price: <?php echo $productData['price']; ?></p>

        <!-- Checkout button -->
        <div id="paypal-button"></div>
    </div>
    <script>
        paypal.Button.render({
            // Configure environment
            env: '<?php echo $paypal->paypalEnv; ?>',
            client: {
                sandbox: '<?php echo $paypal->paypalClientID; ?>',
                production: '<?php echo $paypal->paypalClientID; ?>'
            },
            // Customize button (optional)
            locale: 'en_US',
            style: {
                size: 'small',
                color: 'gold',
                shape: 'pill',
            },
            // Set up a payment
            payment: function(data, actions) {
                // console.log('payment::', data, actions);
                return actions.payment.create({
                    transactions: [{
                        amount: {
                            total: '<?php echo $productData['price']; ?>',
                            currency: 'USD',
                        },
                        item_list: {
                            items: [{
                                "name": "Red Sox Hat",
                                "sku": "001",
                                "price": "<?php echo $productData['price']; ?>",
                                "currency": "USD",
                                "quantity": 1
                            }]
                        },
                        description: "Hat for the best team ever"
                    }],
                    // redirect_urls: {
                    //     return_url: 'https://example.com/success',
                    //     cancel_url: 'https://example.com/cancel'
                    // }
                });
            },
            // Execute the payment
            onAuthorize: function(data, actions) {
                return actions.payment.execute()
                    .then(function() {
                        // Show a confirmation message to the buyer
                        //window.alert('Thank you for your purchase!');
                        // console.log('auth::', data, actions);
                        // Redirect to the payment process page
                        // actions.redirect(); //redirect to success return_url
                        window.location = "process.php?paymentID=" + data.paymentID + "&token=" + data.paymentToken + "&payerID=" + data.payerID + "&pid=<?php echo $productData['id']; ?>";
                    });
            },
            onCancel: function(data, actions) {
                // actions.redirect(); //redirect to cancel_url page/endpoint
                console.log("You have cancled.");
            },

            onError: function(err) {
                console.log("PayPal has encountered error.!");
            }
        }, '#paypal-button');
    </script>
</body>

</html>