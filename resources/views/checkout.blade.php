<!DOCTYPE html>
<html>
<head>
    <title>Stripe Payment</title>
    <!-- Stripe.js の読み込み -->
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <!-- 支払いフォーム -->
    <form id="payment-form">
        <div id="card-element">
            <!-- Stripeが提供するカード要素がここに挿入される -->
        </div>
        <button id="submit">Pay</button>
    </form>

    <script>
        // Stripeオブジェクトを作成
        var stripe = Stripe('{{ env('STRIPE_KEY') }}');

        // Elementsオブジェクトを作成
        var elements = stripe.elements();

        // カード情報入力用のElementを作成して挿入
        var card = elements.create('card');
        card.mount('#card-element');

        // フォーム送信時の処理
        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            // カード情報からトークンを作成
            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    // エラーが発生した場合
                    console.error(result.error.message);
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
                          }
                      })
                      .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>
</body>
</html>
