self.addEventListener('install', (event) => {
    console.log('Service Worker installed');
  });
  
  self.addEventListener('activate', (event) => {
    console.log('Service Worker activated');
  });
  
  self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
  
    // Blok navigasi keluar domain app
    if (url.origin !== self.location.origin) {
      return;
    }
  
    event.respondWith(fetch(event.request));
  });
  
  