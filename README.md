# Dans Akademi Web Sitesi

Dans kursu öğrencileri ve yöneticileri için kurs, etkinlik ve duyuru yönetimi sağlayan, kullanıcı ve yönetici panelleri içeren bir web sitesi.

🔗 **GitHub:** https://github.com/eminenurserbess/Dans-Akademi-Web-Sitesi

## Amaç

Öğrencilerin kurslar, etkinlikler ve duyurular hakkında kolayca bilgi alabilmesi, online başvuru yapabilmesi ve iletişim kanallarına hızlıca ulaşabilmesi hedeflendi. Öğrenciler kendi profilleri üzerinden kayıt oldukları kursları, katılım durumlarını ve ödeme bilgilerini takip edebiliyor. Yönetici paneli ile kurs ve etkinlik ekleme, duyuru paylaşma, katılımcı bilgilerini düzenleme ve raporlama gibi işlevler sağlanıyor.

## Özellikler

- **Genel bilgilendirme sayfaları** — Anasayfa, Hakkımızda, Kurslar, Etkinlikler, Duyurular, Galeri, İletişim
- **Öğrenci girişi** — kayıtlı kullanıcılar için giriş ekranı
- **Kullanıcı paneli** — kurs takvimi, geçmiş kurslar ve ödeme bilgileri takibi
- **Yönetici paneli** — kurs, etkinlik ve duyuru ekleme/düzenleme/silme, kullanıcı bilgilerini yönetme, raporlama
- **RESTful API entegrasyonu** — PHP ve MySQL ile GET, POST, PUT, DELETE işlemleri
- Mobil uyumlu (responsive) tasarım

## Kullanılan Teknolojiler

<p>
  <img src="https://img.shields.io/badge/-HTML5-E34F26?logo=html5&logoColor=white" />
  <img src="https://img.shields.io/badge/-CSS3-1572B6?logo=css3&logoColor=white" />
  <img src="https://img.shields.io/badge/-JavaScript-F7DF1E?logo=javascript&logoColor=black" />
  <img src="https://img.shields.io/badge/-PHP-777BB4?logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/-MySQL-4479A1?logo=mysql&logoColor=white" />
</p>

## Nasıl Geliştirildi

Sitenin genel yapısı HTML ve CSS ile planlandı; anasayfa ve alt sayfalar (duyurular, etkinlikler, kurslar, galeri, hakkımızda, iletişim) tasarlandı. Kullanıcı paneli ile öğrencilerin kurs takvimi, geçmiş kurslar ve ödeme bilgilerini takip edebileceği bölümler eklendi. Yönetici panelinde kurs, etkinlik, duyuru ve kullanıcı bilgilerini yönetebilecekleri sayfalar oluşturuldu.

Veritabanı bağlantısı PDO ile MySQL'e kuruldu, karakter desteği için `utf8mb4` ayarlandı. Admin panelindeki her modül (Hakkımızda, Yöneticilerimiz, İletişim, Kurslar, Etkinlikler, Duyurular, Kullanıcı Bilgileri) için ayrı bir PHP dosyasında GET, POST, PUT, DELETE metodlarıyla RESTful API uç noktaları yazıldı; ön yüzde JavaScript Fetch API ile bu API'lere istek gönderilerek sayfalar dinamik hale getirildi.


## Proje Yapısı

```
Dans-Akademi-Web-Sitesi/
└── Dans/
    ├── index.html               # Anasayfa
    ├── ogrenci-girisi.html      # Öğrenci giriş sayfası
    ├── kurslar.html             # Kurs listesi
    ├── kurs-detay.html          # Kurs detay sayfası
    ├── etkinlikler.html         # Etkinlik listesi
    ├── duyurular.html           # Duyuru listesi
    ├── galeri.html              # Galeri
    ├── hakkımızda.html
    ├── iletisim.html
    ├── script.js
    ├── admin-panel/             # Yönetici paneli sayfaları ve API (PHP)
    ├── kullanici-panel/         # Öğrenci paneli sayfaları
    ├── css/
    ├── fonts/

    ├── images/
    └── videos/
```

## Yol Haritası

- [ ] Ödeme sisteminin gerçek bir ödeme altyapısıyla entegrasyonu
- [ ] Öğrenci giriş sisteminde oturum yönetiminin (session) güçlendirilmesi
- [ ] Raporlama sayfasının detaylandırılması (grafik, filtreleme)
- [ ] Responsive tasarımın tüm admin panel sayfalarında tamamlanması

