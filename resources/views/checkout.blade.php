<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment</title>
    <!-- Stripe.js の読み込み -->
    <script src="https://js.stripe.com/v3/"></script>
    <!-- モダンなデザインを実現するためのスタイル -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .payment-container {
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 400px;
            max-width: 90%;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        #card-element {
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        #submit {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #submit:hover {
            background-color: #218838;
        }
        #submit:disabled {
            background-color: #ccc;
        }
        .alert {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #d9534f;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Make a Payment</h2>
        <!-- 支払いフォーム -->
        <form id="payment-form">
            <div id="card-element">
                <!-- Stripeが提供するカード要素がここに挿入される -->
            </div>
            <button id="submit">Pay</button>
        </form>
        <div class="alert" id="payment-alert" style="display:none;"></div>
    </div>

    <script>
        // Stripeオブジェクトを作成
        var stripe = Stripe('{{ env('STRIPE_KEY') }}');

        // Elementsオブジェクトを作成
        var elements = stripe.elements();

        // カード情報入力用のElementを作成して挿入
        var card = elements.create('card', {
            style: {
                base: {
                    color: '#32325d',
                    fontFamily: 'Arial, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#888'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            }
        });
        card.mount('#card-element');

        // フォーム送信時の処理
        var form = document.getElementById('payment-form');
        var submitButton = document.getElementById('submit');
        var alertBox = document.getElementById('payment-alert');

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';

            // カード情報からトークンを作成
            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    // エラーが発生した場合
                    alertBox.style.display = 'block';
                    alertBox.textContent = result.error.message;
                    submitButton.disabled = false;
                    submitButton.textContent = 'Pay';
                } else {
                    // トークンをサーバーに送信
                    fetch('/charge', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ token: result.token.id })
                    }).then(response => response.json())
                      .then(data => {
                          if (data.success) {
                              alert('Payment Success!');
                          } else {
                              alert('Payment Failed!');
                              alertBox.style.display = 'block';
                              alertBox.textContent = 'Payment failed, please try again.';
                          }
                          submitButton.disabled = false;
                          submitButton.textContent = 'Pay';
                      })
                      .catch(error => {
                          console.error('Error:', error);
                          alertBox.style.display = 'block';
                          alertBox.textContent = 'An error occurred, please try again.';
                          submitButton.disabled = false;
                          submitButton.textContent = 'Pay';
                      });
                }
            });
        });
    </script>
</body>
</html>
