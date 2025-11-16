@component('mail::message')
    Siparişiniz Alındı
    Merhaba {{ $order->user->name }},
    Siparişiniz alınmıştır.
    Sipariş No: #{{ $order->id }}
    Ürünler:
    @foreach($order->items as $item)
        - {{ $item->product->name }} x {{ $item->quantity }} — {{ number_format($item->price,2) }}₺
    @endforeach
    Toplam: {{ number_format($order->total_amount, 2) }}₺**
    Teşekkürler,
    <br>
    {{ config('app.name') }}
@endcomponent
