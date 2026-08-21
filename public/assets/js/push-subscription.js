async function subscribePush() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.warn('Push notifications not supported by browser.');
        return;
    }
    
    // Convert base64 VAPID key to Uint8Array
    function urlB64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    try {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            console.warn('Push notification permission denied.');
            return;
        }

        const reg = await navigator.serviceWorker.ready;
        if (!reg) return;

        // VAPID_PUBLIC_KEY diinject lewat template footer atau didefine secara global
        if (typeof VAPID_PUBLIC_KEY === 'undefined' || !VAPID_PUBLIC_KEY) {
            console.error('VAPID_PUBLIC_KEY is missing');
            return;
        }

        const appServerKey = urlB64ToUint8Array(VAPID_PUBLIC_KEY);
        
        // Cek kalau sudah subscribe
        let sub = await reg.pushManager.getSubscription();
        if (sub) {
            // Unsubscribe yang lama jika key beda
            const currentKey = sub.options.applicationServerKey;
            if (currentKey && btoa(String.fromCharCode.apply(null, new Uint8Array(currentKey))) !== btoa(String.fromCharCode.apply(null, appServerKey))) {
                await sub.unsubscribe();
                sub = null;
            }
        }

        if (!sub) {
            sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: appServerKey
            });
        }

        // Kirim ke backend
        await fetch(base_url + '/api/notif/push/subscribe', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(sub.toJSON())
        });
        
        console.log('Push notification subscribed successfully.');
    } catch (e) {
        console.error('Error subscribing to push notifications', e);
    }
}

// Subscribe otomatis pas web diload kalo udah support dan secure
window.addEventListener('load', () => {
    // delay sedikit biar ga ngeblock initial render
    setTimeout(() => {
        subscribePush();
    }, 5000);
});
