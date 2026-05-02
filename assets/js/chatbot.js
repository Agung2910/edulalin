// ===== CHATBOT CUSTOMER SERVICE - MODERN & SMART =====
(function() {
    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotWindow = document.getElementById('chatbotWindow');
    const chatbotMinimize = document.getElementById('chatbotMinimize');
    const chatbotBody = document.getElementById('chatbotBody');
    const chatbotForm = document.getElementById('chatbotForm');
    const chatbotInput = document.getElementById('chatbotInput');
    const quickReplyBtns = document.querySelectorAll('.quick-reply-btn');

    // ===== STATE MANAGEMENT =====
    let conversationHistory = [];
    let isTyping = false;

    // ===== TOGGLE CHAT =====
    function toggleChat() {
        chatbotToggle.classList.toggle('active');
        chatbotWindow.classList.toggle('active');
        
        if (chatbotWindow.classList.contains('active')) {
            chatbotInput.focus();
            // Remove notification badge
            const badge = document.querySelector('.chatbot-badge');
            if (badge) {
                badge.style.opacity = '0';
                setTimeout(() => badge.style.display = 'none', 300);
            }
        }
    }

    chatbotToggle.addEventListener('click', toggleChat);
    chatbotMinimize.addEventListener('click', toggleChat);

    // ===== SCROLL TO BOTTOM =====
    function scrollToBottom() {
        chatbotBody.scrollTo({
            top: chatbotBody.scrollHeight,
            behavior: 'smooth'
        });
    }

    // ===== ADD MESSAGE =====
    function addMessage(text, isUser = false, options = {}) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user-message' : 'bot-message'}`;
        
        // Avatar
        const avatarDiv = document.createElement('div');
        avatarDiv.className = 'message-avatar';
        avatarDiv.innerHTML = `
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
            </svg>
        `;

        // Content
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        
        const textP = document.createElement('p');
        if (options.isWelcome) {
            textP.classList.add('welcome-msg');
        }
        textP.innerHTML = text; // Allow HTML for formatting
        
        const timeSpan = document.createElement('span');
        timeSpan.className = 'message-time';
        const now = new Date();
        timeSpan.textContent = now.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        contentDiv.appendChild(textP);
        contentDiv.appendChild(timeSpan);
        messageDiv.appendChild(avatarDiv);
        messageDiv.appendChild(contentDiv);
        
        chatbotBody.appendChild(messageDiv);
        
        // Save to history
        conversationHistory.push({
            text: text,
            isUser: isUser,
            timestamp: now
        });
        
        setTimeout(scrollToBottom, 100);
    }

    // ===== TYPING INDICATOR =====
    function showTyping() {
        if (isTyping) return;
        isTyping = true;
        
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chat-message bot-message typing-indicator';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `
            <div class="message-avatar">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                </svg>
            </div>
            <div class="message-content">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        `;
        chatbotBody.appendChild(typingDiv);
        scrollToBottom();
    }

    function removeTyping() {
        const typing = document.getElementById('typingIndicator');
        if (typing) {
            typing.remove();
        }
        isTyping = false;
    }

    // ===== SMART BOT RESPONSES =====
    const responses = {
        // Greeting
        greeting: [
            "Halo! 👋 Senang bisa membantu Anda hari ini! Ada yang bisa saya bantu?",
            "Hai! Selamat datang di <strong>Edu Lalin</strong>! 🚗 Ada yang ingin Anda tanyakan?",
            "Halo! Semoga harimu menyenangkan! 😊 Bagaimana saya bisa membantu?"
        ],
        
        // About Edu Lalin
        about: [
            "<strong>Edu Lalin</strong> adalah platform pembelajaran keselamatan lalu lintas yang dirancang khusus untuk siswa <strong>SD, SMP, dan SMA</strong>. 🎓<br><br>✨ <strong>Fitur unggulan:</strong><br>📚 Materi interaktif per jenjang<br>🎯 Kuis adaptif & peringkat<br>🏆 Skor & sertifikat digital<br>📱 Bisa diakses dari HP & laptop",
            "Platform edukasi keselamatan berkendara terlengkap! 🚦<br><br>Kami menyediakan:<br>• <strong>Modul lengkap</strong> SD-SMA<br>• <strong>Kuis interaktif</strong> dengan scoring otomatis<br>• <strong>Materi SIM</strong> berdasarkan usia<br>• <strong>Bisa diakses kapan saja!</strong>"
        ],
        
        // Registration
        register: [
            "Cara daftar <strong>super mudah</strong>! 🎉<br><br><strong>Langkah-langkah:</strong><br>1️⃣ Klik tombol <strong>\"Daftar\"</strong> di pojok kanan atas<br>2️⃣ Isi data diri (nama, email, password)<br>3️⃣ Pilih jenjang sekolah kamu<br>4️⃣ Klik <strong>\"Daftar\"</strong> dan akun langsung aktif! ✅<br><br>Gratis dan tanpa verifikasi ribet!",
            "Gampang banget kok! 😊<br><br>✅ Buka halaman <strong>Daftar</strong><br>✅ Masukkan nama & email<br>✅ Buat password (min. 6 karakter)<br>✅ Pilih jenjang: SD / SMP / SMA<br>✅ Klik daftar, selesai! 🎊<br><br>Nanti langsung bisa login dan mulai belajar!"
        ],
        
        // Modules
        modules: [
            "Modul <strong>Edu Lalin</strong> dibagi berdasarkan jenjang pendidikan: 📚<br><br><strong>🎒 SD (Kelas 1-6)</strong><br>Materi dasar keselamatan jalan, rambu lalu lintas, dan etika pejalan kaki.<br><br><strong>🎓 SMP (Kelas 7-9)</strong><br>Pendalaman aturan lalu lintas, keselamatan berkendara sepeda, dan tanggung jawab pengguna jalan.<br><br><strong>🎯 SMA (Kelas 10-12)</strong><br>Studi kasus nyata, persiapan SIM, dan analisis kecelakaan lalu lintas.",
            "Setiap jenjang punya materi khusus! 🎓<br><br>📌 <strong>SD:</strong> Rambu & etika dasar<br>📌 <strong>SMP:</strong> Aturan & keselamatan sepeda<br>📌 <strong>SMA:</strong> Studi kasus & persiapan SIM<br><br>Kamu bisa akses modul sesuai jenjang di halaman <strong>\"Modul per Jenjang\"</strong>!"
        ],
        
        // Quiz Issues
        quiz_issue: [
            "Ada masalah dengan kuis? 🤔 Coba langkah ini:<br><br>✅ Pastikan sudah <strong>login</strong><br>✅ Pilih <strong>jenjang yang sesuai</strong><br>✅ <strong>Refresh halaman</strong> (Ctrl+R)<br>✅ Coba <strong>clear cache</strong> browser<br><br>Kalau masih error, screenshot dan kirim ke admin ya! 📧",
            "Tenang, ada solusinya! 💪<br><br><strong>Troubleshooting:</strong><br>1. Logout → Login lagi<br>2. Pastikan koneksi internet stabil<br>3. Coba browser lain (Chrome/Firefox)<br>4. Clear cookies & cache<br><br>Masih bermasalah? Hubungi <strong>support@edulalin.id</strong>"
        ],
        
        // Forgot Password
        forgot_password: [
            "Lupa password? Gak masalah! 🔐<br><br><strong>Cara reset:</strong><br>1️⃣ Klik <strong>\"Lupa Password\"</strong> di halaman login<br>2️⃣ Masukkan <strong>email terdaftar</strong><br>3️⃣ Cek <strong>inbox/spam</strong> email kamu<br>4️⃣ Klik link reset & buat password baru<br><br>Link berlaku 24 jam ya! ⏰",
            "Tenang, bisa di-reset kok! 😊<br><br>👉 Klik menu <strong>\"Lupa Password\"</strong><br>👉 Input email yang kamu pakai daftar<br>👉 Cek email masuk (atau folder spam)<br>👉 Ikuti instruksi reset password<br><br>Selesai! Bisa login lagi deh! 🎉"
        ],
        
        // Contact Admin
        contact: [
            "Mau hubungi admin? Ada beberapa cara! 📞<br><br><strong>📧 Email:</strong> support@edulalin.id<br><strong>📱 WhatsApp:</strong> 0812-3456-7890<br><strong>🕒 Jam kerja:</strong> Senin-Jumat, 08:00-17:00 WIB<br><br>Kami akan respon maksimal <strong>1x24 jam</strong>! ⚡",
            "Hubungi kami lewat: 📬<br><br>✉️ <strong>Email:</strong> support@edulalin.id<br>💬 <strong>WhatsApp:</strong> 0812-3456-7890<br>📍 <strong>Kantor:</strong> Jakarta Selatan<br><br>CS kami <strong>fast response</strong> kok! 🚀"
        ],
        
        // Thanks
        thanks: [
            "Sama-sama! 😊 Senang bisa membantu! Ada lagi yang ingin ditanyakan?",
            "Dengan senang hati! 🤗 Jangan ragu tanya lagi kalau butuh bantuan ya!",
            "You're welcome! ✨ Semoga belajarnya lancar! Ada yang lain?"
        ],
        
        // Default
        default: [
            "Hmm, saya kurang paham pertanyaan kamu 🤔<br><br>Coba pilih menu di bawah atau jelaskan lebih detail ya!",
            "Maaf, saya belum mengerti maksudnya 😅<br><br>Bisa diperjelas atau pilih salah satu menu bantuan di bawah?",
            "Waduh, pertanyaan ini baru buat saya! 🙈<br><br>Mungkin bisa hubungi admin untuk info lebih detail: <strong>support@edulalin.id</strong>"
        ]
    };

    // ===== GET SMART RESPONSE =====
    function getSmartResponse(userMessage) {
        const msg = userMessage.toLowerCase();
        
        // Greeting
        if (msg.match(/^(hai|halo|hi|hello|hey|pagi|siang|sore|malam)/)) {
            return getRandomResponse(responses.greeting);
        }
        
        // Thanks
        if (msg.match(/(terima kasih|thanks|makasih|tengkyu|thank you)/)) {
            return getRandomResponse(responses.thanks);
        }
        
        // About Edu Lalin
        if (msg.match(/(apa itu|tentang|edu lalin|platform ini|website ini|fitur)/)) {
            return getRandomResponse(responses.about);
        }
        
        // Register
        if (msg.match(/(daftar|register|sign up|cara mendaftar|buat akun|akun baru)/)) {
            return getRandomResponse(responses.register);
        }
        
        // Modules
        if (msg.match(/(modul|jenjang|materi|pelajaran|sd|smp|sma|kelas)/)) {
            return getRandomResponse(responses.modules);
        }
        
        // Quiz issues
        if (msg.match(/(kuis|quiz|tidak bisa|error|masalah|bug|rusak|loading)/)) {
            return getRandomResponse(responses.quiz_issue);
        }
        
        // Forgot password
        if (msg.match(/(lupa password|forgot password|reset password|ganti password)/)) {
            return getRandomResponse(responses.forgot_password);
        }
        
        // Contact
        if (msg.match(/(hubungi|kontak|admin|cs|customer service|email|whatsapp|telpon)/)) {
            return getRandomResponse(responses.contact);
        }
        
        // Default
        return getRandomResponse(responses.default);
    }

    function getRandomResponse(responseArray) {
        return responseArray[Math.floor(Math.random() * responseArray.length)];
    }

    // ===== HANDLE FORM SUBMIT =====
    chatbotForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const userMessage = chatbotInput.value.trim();
        if (!userMessage) return;
        
        // Add user message
        addMessage(userMessage, true);
        chatbotInput.value = '';
        
        // Show typing with random delay
        showTyping();
        
        // Bot response
        const delay = 800 + Math.random() * 1200;
        setTimeout(() => {
            removeTyping();
            const botResponse = getSmartResponse(userMessage);
            addMessage(botResponse, false);
        }, delay);
    });

    // ===== HANDLE QUICK REPLIES =====
    quickReplyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const reply = this.getAttribute('data-reply');
            
            // Add user message
            addMessage(reply, true);
            
            // Show typing
            showTyping();
            
            // Bot response
            setTimeout(() => {
                removeTyping();
                const botResponse = getSmartResponse(reply);
                addMessage(botResponse, false);
            }, 600 + Math.random() * 600);
        });
    });

    // ===== CLOSE ON OUTSIDE CLICK =====
    document.addEventListener('click', function(e) {
        if (chatbotWindow.classList.contains('active') && 
            !chatbotWindow.contains(e.target) && 
            !chatbotToggle.contains(e.target)) {
            toggleChat();
        }
    });

    // Prevent closing when clicking inside
    chatbotWindow.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // ===== ENTER TO SEND =====
    chatbotInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatbotForm.dispatchEvent(new Event('submit'));
        }
    });

    // ===== AUTO GREETING (Optional) =====
    setTimeout(() => {
        if (!chatbotWindow.classList.contains('active')) {
            // Show notification badge
            const badge = document.querySelector('.chatbot-badge');
            if (badge) {
                badge.style.display = 'flex';
                badge.style.opacity = '1';
            }
        }
    }, 5000);

})();