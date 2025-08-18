document.addEventListener("DOMContentLoaded", function () {
  // ----------- Mobil Navigasyon İşlevselliği -----------
  const mobileNavToggle = document.querySelector(".mobile-nav-toggle");
  const mobileMenu = document.querySelector(".mobile-menu");
  const mobileMenuClose = document.querySelector(".mobile-menu-close");

  if (mobileNavToggle && mobileMenu && mobileMenuClose) {
    mobileNavToggle.addEventListener("click", () => {
      mobileMenu.classList.add("open"); // "active" yerine "open" kullandım, HTML'deki sınıfınızla tutarlı olması için
      document.body.style.overflow = "hidden"; // Arka planı kaydırmayı engeller
    });

    mobileMenuClose.addEventListener("click", () => {
      mobileMenu.classList.remove("open"); // "active" yerine "open" kullandım
      document.body.style.overflow = ""; // Arka plan kaydırmayı geri açar
    });
  }

  // ----------- Video İşlevselliği (Genellikle 'index.html' için) -----------
  const video = document.getElementById("background-video");

  if (video) {
    // Sadece video elementi varsa çalışır
    video.addEventListener("error", function () {
      console.error("Video yüklenemedi veya oynatılamadı.");
      // Video başarısız olursa yedek bir arka plan görüntüsü ayarla
      document.body.style.backgroundImage = 'url("images/fallback-dance-image.jpg")';
      document.body.style.backgroundSize = "cover";
      document.body.style.backgroundPosition = "center";
    });

    // Sayfa tamamen yüklendiğinde videoyu oynatmaya çalış
    window.addEventListener("load", () => {
      // readyState 3 veya daha yüksekse (CAN_PLAY_THROUGH veya HAVE_ENOUGH_DATA)
      if (video.readyState >= 3) {
        video.play().catch((error) => {
          console.warn("Video otomatik oynatılamadı (muhtemelen tarayıcı kısıtlamaları):", error);
        });
      } else {
        // Video hala yükleniyorsa 'canplaythrough' eventini bekle
        video.addEventListener("canplaythrough", () => {
          video.play().catch((error) => {
            console.warn("Video otomatik oynatılamadı (canplaythrough event sonrası):", error);
          });
        });
      }
    });
  }

  // ----------- Scroll Indicator (Genellikle 'index.html' veya ana sayfa için) -----------
  const scrollIndicator = document.querySelector(".scroll-indicator");
  if (scrollIndicator) {
    // Sadece scroll indicator elementi varsa çalışır
    window.addEventListener("scroll", () => {
      if (window.scrollY > 100) {
        // 100px aşağı kaydırıldığında gizle
        scrollIndicator.style.opacity = "0";
        scrollIndicator.style.transition = "opacity 0.5s ease-out";
      } else {
        scrollIndicator.style.opacity = "0.8"; // Başlangıçta görünür yap
      }
    });
  }

  // ----------- Duyuru Modalı İşlevselliği (Genellikle 'duyurular.html' için) -----------
  const announcementModal = document.getElementById("announcementModal");
  const announcementCards = document.querySelectorAll(".announcement-card"); // Tüm duyuru kartlarını seçer

  if (announcementModal && announcementCards.length > 0) {
    // Modal ve kartlar varsa çalışır
    const modalTitle = document.getElementById("modalTitle");
    const modalDate = document.getElementById("modalDate");
    const modalText = document.getElementById("modalText");
    const announcementCloseButton = announcementModal.querySelector(".close-button");

    announcementCards.forEach((card) => {
      card.addEventListener("click", () => {
        const title = card.dataset.title;
        const date = card.dataset.date;
        const text = card.dataset.text;

        if (modalTitle) modalTitle.textContent = title;
        if (modalDate) modalDate.textContent = date;
        if (modalText) modalText.textContent = text;

        announcementModal.classList.add("active"); // Modalı aktif hale getir (CSS'inizde 'active' sınıfı olmalı)
        document.body.style.overflow = "hidden"; // Sayfa kaydırmayı kapat
      });
    });

    if (announcementCloseButton) {
      announcementCloseButton.addEventListener("click", () => {
        announcementModal.classList.remove("active"); // Modalı kapat
        document.body.style.overflow = ""; // Sayfa kaydırmayı geri aç
      });
    }

    // Modal dışına tıklanınca kapat
    window.addEventListener("click", (event) => {
      if (event.target === announcementModal) {
        announcementModal.classList.remove("active");
        document.body.style.overflow = "";
      }
    });

    // ESC tuşuna basılınca kapat
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && announcementModal.classList.contains("active")) {
        announcementModal.classList.remove("active");
        document.body.style.overflow = "";
      }
    });
  }

  // ----------- Duyuru Sayfalama İşlevselliği (SADECE 'duyurular.html' için) -----------
  const announcementsContainer = document.querySelector(".announcement-grid");
  const prevButton = document.getElementById("prevPage");
  const nextPage = document.getElementById("nextPage");
  const pageNumbersContainer = document.getElementById("pageNumbers");

  // Sadece duyurular sayfasında gerekli elementler varsa bu bloğu çalıştır
  if (announcementsContainer && prevButton && nextPage && pageNumbersContainer) {
    const announcementCardsPagination = Array.from(announcementsContainer.querySelectorAll(".announcement-card"));
    const cardsPerPage = 4; // Her sayfada gösterilecek duyuru sayısı
    let currentPage = 1;
    const totalPages = Math.ceil(announcementCardsPagination.length / cardsPerPage);

    function displayPage(page) {
      announcementCardsPagination.forEach((card) => {
        card.style.display = "none";
      });

      const start = (page - 1) * cardsPerPage;
      const end = start + cardsPerPage;
      for (let i = start; i < end && i < announcementCardsPagination.length; i++) {
        announcementCardsPagination[i].style.display = "flex"; // Duyuru kartlarınız flex olduğu için 'flex' kullandım
      }

      updatePaginationButtons();
      updatePageNumbers();
    }

    function updatePaginationButtons() {
      prevButton.disabled = currentPage === 1;
      nextPage.disabled = currentPage === totalPages;
    }

    function updatePageNumbers() {
      pageNumbersContainer.innerHTML = ""; // Önceki sayfa numaralarını temizle
      for (let i = 1; i <= totalPages; i++) {
        const pageNumber = document.createElement("span");
        pageNumber.classList.add("page-number");
        pageNumber.textContent = i;
        if (i === currentPage) {
          pageNumber.classList.add("active");
        }
        pageNumber.addEventListener("click", () => {
          currentPage = i;
          displayPage(currentPage);
          // Sayfa atlandığında ekranı yukarı kaydırabilirsiniz (isteğe bağlı)
          window.scrollTo({ top: announcementsContainer.offsetTop - 100, behavior: "smooth" });
        });
        pageNumbersContainer.appendChild(pageNumber);
      }
    }

    // Olay Dinleyicileri
    prevButton.addEventListener("click", () => {
      if (currentPage > 1) {
        currentPage--;
        displayPage(currentPage);
        window.scrollTo({ top: announcementsContainer.offsetTop - 100, behavior: "smooth" });
      }
    });

    nextPage.addEventListener("click", () => {
      if (currentPage < totalPages) {
        currentPage++;
        displayPage(currentPage);
        window.scrollTo({ top: announcementsContainer.offsetTop - 100, behavior: "smooth" });
      }
    });

    // İlk sayfayı yükle
    displayPage(currentPage);
  }

  // ----------- Genel Modal Açma/Kapama Fonksiyonları -----------
  // Bu fonksiyonlar artık daha genel ve tekrar kullanılabilir
  function openModal(modalElement, textToDisplay, spanIdToUpdate) {
    const spanElement = modalElement.querySelector(`#${spanIdToUpdate}`);
    if (spanElement) {
      spanElement.textContent = textToDisplay;
    }
    modalElement.classList.add("show"); // Modalı göstermek için 'show' sınıfını kullanıyoruz
    document.body.style.overflow = "hidden"; // Arka planı kaydırmayı engeller
    // Modalı açtıktan sonra ilk inputa odaklan (isteğe bağlı, opsiyonel)
    const firstInput = modalElement.querySelector('input[type="text"], input[type="email"], input[type="tel"]');
    if (firstInput) {
      firstInput.focus();
    }
  }

  function closeModal(modalElement) {
    modalElement.classList.remove("show"); // Modalı gizlemek için 'show' sınıfını kaldırıyoruz
    document.body.style.overflow = ""; // Arka plan kaydırmayı geri açar
    // Formu sıfırla (eğer içinde form varsa)
    const form = modalElement.querySelector("form");
    if (form) {
      form.reset();
    }
  }

  // ----------- ETKİNLİK KAYIT OL MODALI (Genellikle 'etkinlikler.html' için) -----------
  const participationModal = document.getElementById("participationModal");
  // Eğer etkinlik kartlarınız farklı bir sınıfa sahipse burada güncelleyin
  const eventJoinButtons = document.querySelectorAll(".event-card .btn-primary");

  if (participationModal && eventJoinButtons.length > 0) {
    const participationCloseButton = participationModal.querySelector(".close-button");
    const participationForm = participationModal.querySelector(".participation-form");

    eventJoinButtons.forEach((button) => {
      button.addEventListener("click", function (event) {
        event.preventDefault();
        const eventCard = this.closest(".event-card");
        const eventName = eventCard ? eventCard.querySelector("h3").textContent : "Etkinlik";
        openModal(participationModal, eventName, "modalEventName"); // modalEventName span ID'si participationModal içinde olmalı
      });
    });

    if (participationCloseButton) {
      participationCloseButton.addEventListener("click", () => closeModal(participationModal));
    }

    // Modal dışına tıklayınca kapat
    window.addEventListener("click", function (event) {
      if (event.target === participationModal) {
        closeModal(participationModal);
      }
    });

    // ESC tuşuna basılınca kapat
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && participationModal.classList.contains("show")) {
        closeModal(participationModal);
      }
    });

    if (participationForm) {
      participationForm.addEventListener("submit", function (event) {
        event.preventDefault();
        alert("Başvurunuz gönderildi! En kısa sürede sizinle iletişime geçilecektir.");
        closeModal(participationModal);
        // Formu sıfırlama işlemi closeModal içinde zaten var, burada tekrar etmeye gerek yok
      });
    }
  }

  // ----------- KURS KATILMAK İSTİYORUM MODALI (Genellikle 'kurslar.html' için) -----------
  const enrollmentModal = document.getElementById("enrollmentModal");
  const courseEnrollButtons = document.querySelectorAll(".course-showcase .btn-primary.open-enrollment-modal");

  if (enrollmentModal && courseEnrollButtons.length > 0) {
    const enrollmentCloseButton = enrollmentModal.querySelector(".close-button");
    const enrollmentForm = enrollmentModal.querySelector("#enrollmentForm"); // ID ile seçmek daha güvenli

    courseEnrollButtons.forEach((button) => {
      button.addEventListener("click", function (event) {
        event.preventDefault();
        const courseTitle = this.dataset.courseTitle || "Kurs";
        openModal(enrollmentModal, courseTitle, "modalCourseTitle"); // modalCourseTitle span ID'si enrollmentModal içinde olmalı
      });
    });

    if (enrollmentCloseButton) {
      enrollmentCloseButton.addEventListener("click", () => closeModal(enrollmentModal));
    }

    // Modal dışına tıklanınca kapat
    window.addEventListener("click", function (event) {
      if (event.target === enrollmentModal) {
        closeModal(enrollmentModal);
      }
    });

    // ESC tuşuna basılınca kapat
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && enrollmentModal.classList.contains("show")) {
        closeModal(enrollmentModal);
      }
    });

    if (enrollmentForm) {
      enrollmentForm.addEventListener("submit", function (event) {
        event.preventDefault();
        alert("Kurs başvurunuz alınmıştır, en kısa sürede sizinle iletişime geçilecektir.");
        closeModal(enrollmentModal);
        // Formu sıfırlama işlemi closeModal içinde zaten var, burada tekrar etmeye gerek yok
      });
    }
  }

  // ----------- SLIDER İŞLEVSELLİĞİ (Genellikle 'index.html' için) -----------
  const slider = document.getElementById("academySlider");
  if (slider) {
    // Sadece slider elementi varsa çalışır
    const prevBtn = document.getElementById("prevSlide");
    const nextBtn = document.getElementById("nextSlide");

    if (!prevBtn || !nextBtn) {
      console.error(
        "Slider navigation buttons not found. Make sure 'prevSlide' and 'nextSlide' IDs exist in HTML for the slider."
      );
      // Eğer butonlar yoksa slider işlevselliğini durdur
      return;
    }

    const images = slider.querySelectorAll("img");
    const imageCount = images.length;
    // Eğer sliderınızda klonlanmış ilk ve son resimler varsa, gerçek resim sayısı 2 eksiktir.
    // HTML yapınızda bu klonlanmış resimlerin (infinity loop için) olduğundan emin olun.
    const realImageCount = imageCount - 2;

    let currentIndex = 1; // Başlangıçta ilk gerçek resim (klonlanmış ilk resimden sonraki)
    let isTransitioning = false;

    // İlk yüklemede ani geçişi önlemek için transition'ı kapat
    slider.style.transition = "none";
    slider.style.transform = `translateX(${-currentIndex * 100}%)`;
    // Kısa bir gecikmeyle transition'ı geri aç
    setTimeout(() => {
      slider.style.transition = "transform 0.5s ease-in-out";
    }, 50);

    const slideTo = (index) => {
      if (isTransitioning) return; // Geçiş devam ederken yeni bir slayt isteğini engelle
      isTransitioning = true;
      currentIndex = index;
      slider.style.transform = `translateX(${-currentIndex * 100}%)`;
    };

    slider.addEventListener("transitionend", () => {
      isTransitioning = false; // Geçiş tamamlandığında bayrağı sıfırla

      // Sonsuz döngü mantığı
      if (currentIndex === 0) {
        // Klonlanmış son resme gelince
        slider.style.transition = "none"; // Ani geçiş yap
        currentIndex = realImageCount; // Gerçek son resme atla
        slider.style.transform = `translateX(${-currentIndex * 100}%)`;
        setTimeout(() => {
          slider.style.transition = "transform 0.5s ease-in-out"; // Geçişi geri aç
        }, 50);
      } else if (currentIndex === imageCount - 1) {
        // Klonlanmış ilk resme gelince
        slider.style.transition = "none"; // Ani geçiş yap
        currentIndex = 1; // Gerçek ilk resme atla
        slider.style.transform = `translateX(${-currentIndex * 100}%)`;
        setTimeout(() => {
          slider.style.transition = "transform 0.5s ease-in-out"; // Geçişi geri aç
        }, 50);
      }
    });

    prevBtn.addEventListener("click", () => {
      if (!isTransitioning) {
        slideTo(currentIndex - 1);
      }
      resetAutoSlide(); // Manuel geçişte otomatik slaytı sıfırla
    });

    nextBtn.addEventListener("click", () => {
      if (!isTransitioning) {
        slideTo(currentIndex + 1);
      }
      resetAutoSlide(); // Manuel geçişte otomatik slaytı sıfırla
    });

    // Otomatik Slayt İşlevselliği
    let autoSlideInterval;

    const startAutoSlide = () => {
      autoSlideInterval = setInterval(() => {
        if (!isTransitioning) {
          slideTo(currentIndex + 1);
        }
      }, 3000); // 3 saniyede bir slayt değiştir
    };

    const stopAutoSlide = () => {
      clearInterval(autoSlideInterval);
    };

    const resetAutoSlide = () => {
      stopAutoSlide();
      startAutoSlide();
    };

    startAutoSlide(); // Otomatik slaytı başlat

    // Fare slayt üzerine geldiğinde otomatik slaytı durdur, çıktığında devam ettir
    slider.parentElement.addEventListener("mouseenter", stopAutoSlide);
    slider.parentElement.addEventListener("mouseleave", startAutoSlide);
  }
}); // DOMContentLoaded sonu
