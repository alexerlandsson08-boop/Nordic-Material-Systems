// translations.js
const translations = {
    en: {
        "nav.book-appointment": "Book appointment",
        "nav.products": "Products",
        "nav.team": "Team",
        "nav.contact": "Contact",
        "title.main": "Nordic Material Systems",
        "hero.description": "Nordic Material Systems is not a vision of a perfect future – it is a realistic, feasible solution to a concrete problem that exists here and now.",
        "about.title": "What actually happens to all the clothes when we no longer use them?",
        "about.description": "Nordic Material Systems offers circular and sustainable textile solutions for companies by recycling and refining textiles. The company is based on purchasing and refining leftover textiles from companies. These textiles are restored, processed and converted into new yarn and new, fresh textile products of high quality. The company works with subscription-based collaborations where companies pay an ongoing fee to have their textiles handled in a sustainable way. Through the subscription, continuous collection, recycling and reuse of the material is ensured, which reduces waste and climate impact while utilizing the companies' resources. The recycled materials are sold on as yarn or finished textiles to companies in fashion, interior design and other textile-related industries. NMS thus creates value both economically and environmentally by extending the life cycle of textiles and contributing to a more circular textile industry in Sweden.",
        "login.back-home": "Back to Home",
        "login.title": "Login",
        "login.company-name": "Company name",
        "login.password": "Password",
        "login.submit": "Log in",
        "register.title": "Create account",
        "register.verify-password": "Verify password",
        "register.email": "Email",
        "register.submit": "Register account",
        "products.title": "Our subscription options"
    },
    sv: {
        "nav.book-appointment": "Boka tid",
        "nav.products": "Produkter",
        "nav.team": "Team",
        "nav.contact": "Kontakt",
        "title.main": "Nordic Material Systems",
        "hero.description": "Nordic Material Systems är inte en vision om en perfekt framtid – det är en realistisk, genomförbar lösning på ett konkret problem som finns här och nu.",
        "about.title": "Vad händer egentligen med alla kläder när vi inte längre använder dem?",
        "about.description": "Nordic Material Systems erbjuder cirkulära och hållbara textillösningar för företag genom att återvinna och förädla textilier. Företaget bygger på att köpa in och förädla överblivna textilier från företag. Dessa textilier återställs, bearbetas och omvandlas till nytt garn och nya, fräscha textilprodukter av hög kvalitet.Företaget arbetar med abonnemangsbaserade samarbeten där företag betalar en löpande avgift för att få sina textilier omhändertagna på ett hållbart sätt. Genom abonnemanget säkerställs en kontinuerlig insamling, återvinning och återanvändning av materialet, vilket minskar avfall och klimatpåverkan samtidigt som företagens resurser tas tillvara.De återvunna materialen säljs vidare som garn eller färdiga textilier till företag inom mode, inredning och andra textilrelaterade branscher. NMS skapar därmed värde både ekonomiskt och miljömässigt genom att förlänga textiliers livscykel och bidra till en mer cirkulär textilindustri i Sverige.",
        "login.back-home": "Tillbaka till hem",
        "login.title": "Logga in",
        "login.company-name": "Företagsnamn",
        "login.password": "Lösenord",
        "login.submit": "Logga in",
        "register.title": "Skapa konto",
        "register.verify-password": "Verifiera lösenord",
        "register.email": "E-post",
        "register.submit": "Registrera konto",
        "products.title": "Våra prenumerationsalternativ"
    }
};

let currentLang = localStorage.getItem('language') || 'sv';

function setLanguage(lang) {
    currentLang = lang;
    localStorage.setItem('language', lang);
    updateTexts();
}

function updateTexts() {
    document.querySelectorAll('[data-i18n]').forEach(element => {
        const key = element.getAttribute('data-i18n');
        if (translations[currentLang] && translations[currentLang][key]) {
            element.textContent = translations[currentLang][key];
        }
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(element => {
        const key = element.getAttribute('data-i18n-placeholder');
        if (translations[currentLang] && translations[currentLang][key]) {
            element.placeholder = translations[currentLang][key];
        }
    });
    // Update document lang attribute
    document.documentElement.lang = currentLang;
    // Update button text
    const toggleBtn = document.getElementById('lang-toggle');
    if (toggleBtn) {
        toggleBtn.textContent = currentLang === 'sv' ? '🌎 EN' : '🌎 SV';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', updateTexts);