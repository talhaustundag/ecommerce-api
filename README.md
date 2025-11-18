🛒 Ecommerce API

Laravel + PostgreSQL ile geliştirilmiş RESTful E-Ticaret API Projesi

📌 Proje Özeti

Bu proje, kullanıcı yönetimi, ürün-kategori yönetimi, sepet ve sipariş mekanizmalarını içeren tam işlevsel bir E-Ticaret API’sidir.
API tamamen RESTful, JSON Response, Sanctum Authentication, Validation, Swagger/OpenAPI Dokümantasyonu, Stock Kontrol, Sipariş Email Bildirimleri gibi özellikler içerir.

🚀 Kurulum Adımları

1️⃣ Depoyu Klonlayın

git clone https://github.com/talhaustundag/ecommerce-api.git

cd ecommerce-api

2️⃣ Environment Ayarları

cp .env.example .env

.env dosyasında PostgreSQL bağlantısını yapılandır:

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ecommerce_api
DB_USERNAME=postgres
DB_PASSWORD=123456

Mail gönderimi için gerekli environment ayarları:

MAIL_MAILER=log
MAIL_FROM_ADDRESS=no-reply@ecommerce.local
MAIL_FROM_NAME="Ecommerce API"

Not: Geliştirme ortamında mail içerikleri storage/logs/laravel.log üzerinden görüntülenir.

3️⃣ Bağımlılıkları Kurun

composer install

4️⃣ Uygulama Key Oluşturun

php artisan key:generate

5️⃣ SQL Dump

Sample Data

Proje klasöründe bulunan `ecommerce_api_dump.sql` dosyası aşağıdaki örnek verileri içerir:

• Admin kullanıcı  
• Normal kullanıcı  
• Minimum 5 kategori  
• Her kategoride minimum 5 ürün  

Bu nedenle migrate/seed çalıştırmanıza gerek yoktur.
Tek yapmanız gereken SQL dump dosyasını PostgreSQL’e import etmektir.

Opsiyonel: Eğer veri olmadan temiz kurulum yapmak isterseniz:

'php artisan migrate'

6️⃣ Uygulamayı Başlatın

php artisan serve

🗄 PostgreSQL Başlatma (Elle)

Eğer PostgreSQL'i manuel başlatıyorsan:

cd C:\PostgreSQL\18\bin

pg_ctl -D "C:\PostgreSQL\18\data" start

📘 API Documentation (Swagger)

Swagger UI otomatik oluşturulmuştur:

👉 http://localhost:8000/api/documentation

🔐 Authentication

API, Laravel Sanctum ile korunmaktadır.

Login sonrası response içinde gelen:

"token": "1|xasdlkasd123123..."


header’a eklenmelidir:

Authorization: Bearer <token>

📚 API Endpoint Listesi

👤 Kullanıcı Yönetimi

Method	Endpoint	Açıklama

POST	/api/register	Yeni kullanıcı oluştur

POST	/api/login	Giriş yap

GET	/api/profile	Profil görüntüle

PUT	/api/profile	Profil güncelle

📂 Kategori Yönetimi

Method	Endpoint	Açıklama

GET	/api/categories	Tüm kategorileri listele

POST /api/categories	Yeni kategori oluştur (Admin)

PUT	/api/categories/{id}	Kategori güncelle (Admin)

DELETE	/api/categories/{id}	Kategori sil (Admin)

🛍 Ürün Yönetimi

Method	Endpoint	Açıklama

GET	/api/products	Ürünleri listele (filtreleme + sayfalama)

GET	/api/products/{id}	Ürün detayı

POST	/api/products	Ürün oluştur (Admin)

PUT	/api/products/{id}	Ürün güncelle (Admin)

DELETE	/api/products/{id}	Ürün sil (Admin)

🛒 Sepet Yönetimi

Method	Endpoint	Açıklama

GET	/api/cart	Sepeti Görüntüle

POST /api/cart/add	Sepete ürün ekle

PUT	/api/cart/update	Ürün miktarı güncelle

DELETE	/api/cart/remove/{product_id}	Sepetten ürün çıkar

DELETE	/api/cart/clear	Sepeti temizle

📦 Sipariş Yönetimi

Method	Endpoint	Açıklama

POST /api/orders	Sipariş oluştur

GET	/api/orders	Siparişleri listele

GET	/api/orders/{id}	Sipariş detayı

PUT	/api/orders/{id}/status	Sipariş durumu güncelle (Admin)

🔍 Filtreleme ve Sayfalama (Products)

/api/products endpoint'i aşağıdaki query parametrelerini destekler:

Parametre	Açıklama

page	Sayfa numarası

search	Ürün isminde arama

category_id	Kategori filtresi

min_price / max_price	Fiyat aralığı

brand	Marka filtresi

sort_by	price_asc / price_desc / newest

Örnek:

/api/products?search=iphone&min_price=20000&sort_by=price_desc

🎯 Örnek Response Formatı

Tüm endpointler standart JSON formatı döner:

{
  "success": true,
  "message": "İşlem başarılı",
  "data": {},
  "errors": []
}

🧪 Postman Collection

Projenin test koleksiyonu ekli dosya olarak verilmiştir:

Ecommerce-Api.postman_collection.json

📄 Postman Collection  

[Ecommerce-Api.postman_collection.json](Ecommerce-Api.postman_collection.json)


🧰 Kullanılan Teknolojiler

PHP 8+

Laravel 10.x

PostgreSQL

Laravel Sanctum

Swagger (L5-Swagger)

Mail (Sipariş onayı)

Eloquent ORM

👤 Test Kullanıcıları

Admin Kullanıcı

email: admin@test.com

password: admin123

Normal Kullanıcı

email: user@test.com

password: user.123

Not: Test Kullanıcısı Şifresi

Case dökümanında normal kullanıcı için şu bilgiler istenmişti:

email: user@test.com

password: user123

Ancak proje gereksinimlerinde şifre validation kuralı minimum 8 karakter olarak belirtilmişti:
Password: Zorunlu, minimum 8 karakter

Bu nedenle user123 (7 karakter) geçersiz olduğu için, validation kurallarına uygun olacak şekilde user.123 olarak güncellendi.


✔ Bonus Özellikler

Bu projede aşağıdaki bonuslar uygulanmıştır:

✔ Teknik Bonuslar

• Database Migration Sistemi

Laravel migration yapısı kullanılarak tüm tablo yapıları otomatik oluşturulabilir yapıdadır.

• Logging Sistemi

Tüm kritik işlemler Laravel’in storage/logs/laravel.log dosyasına kaydedilir (örn: sipariş oluşturma, hata yakalama).

• API Documentation (Swagger/OpenAPI)

Tüm endpoint’ler OpenAPI standartlarına uygun olarak dökümante edilmiştir.

Dokümantasyon: http://localhost:8000/api/documentation

• Unit Test Altyapısı Hazır (Kısmen)

Test yapısı kurulmuş olup, bazı migration değişiklikleri nedeniyle otomatik test çalıştırmada hata alınmıştır.

✔ Fonksiyonel Bonuslar

• Ürün Stok Takibi

Sipariş oluşturma sırasında her ürün için stok kontrolü yapılır. Yetersiz stok varsa işlem iptal edilir.

• Email Bildirimi (Sipariş Onayı)

Sipariş oluşturulduğunda kullanıcıya sipariş onay maili gönderilir.

(Geliştirme ortamında mail içerikleri storage/logs/laravel.log üzerinden görüntülenebilir.)

• Gelişmiş Ürün Arama ve Filtreleme

Ürün listesinde:

search

category_id

brand

min_price, max_price

sort_by (price_asc, price_desc, newest)

gibi filtreler desteklenir.

• Admin Dashboard Endpoint’leri

Toplam kullanıcı, sipariş, gelir, en çok satan ürünler gibi istatistikler sunulur.

• Sipariş Durumu Güncelleme

Admin kullanıcı sipariş durumlarını (beklemede, hazırlanıyor, kargolandı, teslim edildi, iptal) güncelleyebilir.

📦 SQL Dump

Proje teslim klasöründe:

ecommerce_api_dump.sql

olarak verilmelidir.

📩 İletişim

talhaustundag51@gmail.com

Herhangi bir soruda yardımcı olmaktan memnuniyet duyarım.
