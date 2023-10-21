document.addEventListener("DOMContentLoaded", () => {
    window.onpopstate = () => location.reload();
    console.log("🐘");
    front.core.run();
});

const front = {
    action: {},
    core: {
        URL_VUE_JS: "/assets/script/third/vue.js",
        BASE_HOST: (new URL(window.location)).hostname,
        WORKING: false,
        REGISTRED: {},
        run() {
            Object.keys(front.core.REGISTRED).forEach((querySelector) =>
                document.body.querySelectorAll(querySelector).forEach((el) => {
                    front.core.REGISTRED[querySelector](el);
                    el.removeAttribute("front");
                })
            );
        },
        register(querySelector, action) {
            front.core.REGISTRED[querySelector] = action
        },
        solve: (action) => new Promise(async (resolve, reject) => {
            if (!front.core.WORKING) {
                front.core.WORKING = true;
                document.body.classList.add('front-working');
                let resp = await action();
                front.core.WORKING = false
                document.body.classList.remove('front-working');
                return resolve(resp)
            }
            return reject('awaiting');
        }),
        update: {
            content(content) {
                let el = document.getElementById('front-content')
                el.innerHTML = content;
                el.querySelectorAll("script").forEach((tag) => eval(tag.innerHTML));
                front.core.run();
            },
            layout(content, hash) {
                let el = document.getElementById('front-layout')
                el.innerHTML = content;
                el.dataset.hash = hash;
                el.querySelectorAll("script").forEach((tag) => eval(tag.innerHTML));
                front.core.run();
            },
            location(url) {
                if (url != window.location)
                    history.pushState({ urlPath: url }, null, url);
            },
            head(head) {
                document.title = head.title;
                document.head.querySelector('meta[name="description"]').setAttribute("content", head.description);
                document.head.querySelector('link[rel="icon"]').setAttribute("href", head.favicon);
            }
        },
        load: {
            script(src, callOnLoad = () => { }) {
                if (document.head.querySelectorAll(`script[src="${src}"]`).length > 0) return callOnLoad();
                let script = document.createElement("script");
                script.async = "true";
                script.src = src;
                script.onload = () => callOnLoad();
                document.head.appendChild(script);
            },
            vue(component, inId) {
                front.core.load.script(front.core.URL_VUE_JS, () => Vue.createApp(component).mount(inId));
            },
        },
    },
    go: (url, force = false) => new Promise(async (resolve, reject) => {
        if (!force && url == window.location) return;

        if ((new URL(url)).hostname != front.core.BASE_HOST)
            return await front.redirect(url);

        let hash = document.getElementById('front-layout').dataset.hash;

        let resp = await front.request('get', url, {}, { 'Front-Hash': hash });

        if (!resp.info.elegance)
            return await front.redirect(url);

        if (resp.info.error)
            return;

        front.core.update.head(resp.data.head);

        front.core.update.location(url);

        if (resp.data.hash == hash) {
            front.core.update.content(resp.data.content)
        } else {
            front.core.update.layout(resp.data.content, resp.data.hash)
        }

        window.scrollTo(0, 0);

        return;
    }),
    request: (method, url = null, data = {}, header = {}) => front.core.solve(() =>
        new Promise((resolve, reject) => {
            var xhr = new XMLHttpRequest();

            url = url ?? window.location.href

            xhr.open(method, url, true);
            xhr.setRequestHeader("Front-Request", method);

            for (let key in header)
                xhr.setRequestHeader(key, header[key]);
            xhr.responseType = "json";

            xhr.onload = async () => {
                let resp = xhr.response;

                if (xhr.getResponseHeader("Front-Location")) {
                    front.core.WORKING = false;
                    return resolve(await front.go(xhr.getResponseHeader("Front-Location"), true));
                }

                if (!resp.info.elegance) resp = {
                    info: {
                        elegance: false,
                        error: xhr.status > 399,
                        staus: xhr.status
                    },
                    data: resp,
                };

                return resolve(resp);
            };

            xhr.send(data);
        })
    ),
    redirect: (url) => new Promise((resolve, reject) => {
        window.location.href = url;
        return resolve('ok');
    }),
};

front.core.register("[href][front]", (el) => {
    el.addEventListener("click", (ev) => {
        ev.preventDefault();
        front.go(new URL(el.href ?? el.getAttribute('href'), document.baseURI).href);
    });
});

front.core.register("form[front]", (el) => {
    el.addEventListener("submit", async (ev) => {
        ev.preventDefault();

        let showmessage = el.querySelector(".__alert");

        if (showmessage) showmessage.innerHTML = "";

        let data = new FormData(el);

        el.querySelectorAll('input[type=file]').forEach(input => {
            for (var i = 0; i < input.files.length; i++) {
                data.append(input.getAttribute('name') + "[]", input.files[i]);
            }
        });

        let hash = document.getElementById('front-layout').dataset.hash;

        let resp = await front.request(
            el.getAttribute("method") ?? "post",
            el.action,
            data,
            { 'Front-Hash': hash }
        );

        if (resp.data) {
            front.core.update.head(resp.data.head);

            if (resp.data.hash == hash) {
                front.core.update.content(resp.data.content)
            } else {
                front.core.update.layout(resp.data.content, resp.data.hash)
            }

            window.scrollTo(0, 0);

        } else {

            let action = el.getAttribute(resp.info.error ? "onerror" : "onsuccess");

            if (action) action = eval(action);

            if (action instanceof Function) return action(resp);

            el.querySelectorAll('[data-input].error').forEach(label => {
                label.classList.remove('error')
            })

            if (resp.info.error)
                if (resp.info.field) {
                    let label = el.querySelector(`[data-input=${resp.info.field}]`)
                    if (label)
                        label.classList.add('error')
                }

            if (showmessage) {
                let spanClass = `sts_` + (resp.info.error ? "erro" : "success");
                let message = resp.info.message ?? (resp.info.error ? "erro" : "ok");
                let description = resp.info.description ?? "";

                showmessage.innerHTML =
                    `<span class='sts_${resp.info.status} ${spanClass}'>` +
                    `<span>${message}</span>` +
                    `<span>${description}</span>` +
                    `</span>`;
            }
        }
    });
});

front.core.register('[href]:not([href=""])', (el) => {
    if (el.href == window.location.href)
        el.classList.add('front-active-link');
    else
        el.classList.remove('front-active-link')
})