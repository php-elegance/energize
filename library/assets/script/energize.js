document.addEventListener("DOMContentLoaded", () => {
    window.onpopstate = () => location.reload();
    console.log("⚡");
    document.body.querySelectorAll("script:not([energized])")
        .forEach((tag) => {
            tag.setAttribute('energized', '');
        });
    energize.core.run();
});

const __page = {};

const energize = {
    core: {
        URL_VUE_JS: "/assets/script/third/vue.js",
        BASE_HOST: (new URL(window.location)).hostname,
        WORKING: false,
        REGISTRED: {},
        run() {
            Object.keys(energize.core.REGISTRED).forEach((querySelector) =>
                document.body.querySelectorAll(querySelector).forEach((el) => {
                    energize.core.REGISTRED[querySelector](el);
                    el.setAttribute('energized', '');
                })
            );
            document.body.querySelectorAll("script:not([energized])").forEach((tag) => {
                eval(tag.innerHTML)
                tag.setAttribute('energized', '');
            });
        },
        register(querySelector, action) {
            energize.core.REGISTRED[querySelector] = action
        },
        update: {
            content: (content) => {
                let el = document.getElementById('energize-content')
                el.innerHTML = content;
                energize.core.run();
            },
            layout: (content, hash) => {
                let el = document.getElementById('energize-layout')
                el.innerHTML = content;
                el.dataset.hash = hash;
                energize.core.run();
            },
            location: (url) => {
                if (url != window.location)
                    history.pushState({ urlPath: url }, null, url);
            },
            head: (head) => {
                document.title = head.title;
                document.head.querySelector('meta[name="description"]').setAttribute("content", head.description);
                document.head.querySelector('link[rel="icon"]').setAttribute("href", head.favicon);
            },
            fragment: (e, content, mode) => {
                if (mode) {
                    mode = mode == 1 ? 'beforeend' : 'afterbegin';
                    e.insertAdjacentHTML(mode, content)
                } else {
                    e.innerHTML = content
                }
                energize.core.run();
            },
        },
        load: {
            script(src, call) {
                call = call ?? function () { };

                if (document.head.querySelectorAll(`script[src = "${src}"]`).length > 0)
                    return call();

                let script = document.createElement("script");
                script.async = "true";
                script.src = src;
                script.onload = () => call();
                document.head.appendChild(script);
            },
            vue(component, inId) {
                energize.core.load.script(energize.core.URL_VUE_JS, () => Vue.createApp(component).mount(inId));
            },
        }
    },
    request(url = null, method = 'get', data = {}, header = {}) {
        return new Promise(function (resolve, reject) {

            if (energize.core.WORKING)
                return reject('working');

            energize.core.WORKING = true;
            document.body.classList.add('energize-working');

            var xhr = new XMLHttpRequest();

            url = url ?? window.location.href

            xhr.open(method, url, true);
            xhr.setRequestHeader("Energize-Request", method);

            for (let key in header)
                xhr.setRequestHeader(key, header[key]);

            xhr.responseType = "json";

            xhr.onload = () => {
                energize.core.WORKING = false;
                document.body.classList.remove('energize-working');

                let resp = xhr.response;

                if (xhr.getResponseHeader("Energize-Location")) {
                    energize.go(xhr.getResponseHeader("Energize-Location"), true);
                    return reject('redirect');
                }

                if (!resp.info || !resp.info.elegance) resp = {
                    info: {
                        elegance: false,
                        error: xhr.status > 399,
                        staus: xhr.status
                    },
                    data: resp,
                };

                return resolve(resp)
            };

            xhr.send(data);
        })
    },
    go(url, force = false) {
        if (!force && url == window.location)
            return;

        if ((new URL(url)).hostname != energize.core.BASE_HOST)
            return energize.redirect(url);

        let hash = document.getElementById('energize-layout').dataset.hash;

        energize.request(url, 'get', {}, { 'Energize-Hash': hash })
            .then((resp) => {
                if (!resp.info.elegance)
                    return energize.redirect(url);

                if (resp.info.error)
                    return;

                energize.core.update.head(resp.data.head);

                energize.core.update.location(url);

                if (resp.data.hash == hash)
                    energize.core.update.content(resp.data.content)
                else
                    energize.core.update.layout(resp.data.content, resp.data.hash)

                window.scrollTo(0, 0);
                return;
            }).catch(() => null)
    },
    redirect(url) {
        window.location.href = url;
        return resolve('ok');
    },
    fragment(url, target, mode) {
        energize.request(url, 'get', {}, { 'Energize-Fragment': true })
            .then((resp) => {
                energize.core.update.fragment(target, resp.data.content, mode)
            })
            .catch(() => null)
    }
};

energize.core.register("[href]:not([href='']:not([energized])", (el) => {
    el.addEventListener("click", (ev) => {
        ev.preventDefault();
        let url = new URL(el.href ?? el.getAttribute('href'), document.baseURI).href;
        energize.go(url, document.baseURI)
    })
});

energize.core.register("form:not([energized])", (el) => {
    el.addEventListener("submit", async (ev) => {
        ev.preventDefault();

        let showmessage = el.querySelector(".__alert");

        if (showmessage) showmessage.innerHTML = "";

        el.querySelectorAll('[data-input].error').forEach(label => {
            label.classList.remove('error')
        })

        let data = new FormData(el);

        el.querySelectorAll('input[type=file]').forEach(input => {
            for (var i = 0; i < input.files.length; i++) {
                data.append(input.getAttribute('name') + "[]", input.files[i]);
            }
        });

        energize.request(
            el.action,
            el.getAttribute("method") ?? "post",
            data,
            { 'Energize-Hash': document.getElementById('energize-layout').dataset.hash })
            .then((resp) => {
                if (resp.info.error && el.dataset.error)
                    return eval(el.dataset.error)(resp)

                if (!resp.info.error && el.dataset.success)
                    return eval(el.dataset.success)(resp)

                if (resp.data) {
                    energize.core.update.head(resp.data.head);

                    energize.core.update.location(url);

                    if (resp.data.hash == hash)
                        energize.core.update.content(resp.data.content)
                    else
                        energize.core.update.layout(resp.data.content, resp.data.hash)

                    window.scrollTo(0, 0);
                    return;
                }

                if (resp.info.error && resp.info.field) {
                    let label = el.querySelector(`[data-input=${resp.info.field}]`)
                    if (label) label.classList.add('error')
                }

                if (showmessage) {
                    let spanClass = `sts_` + (resp.info.error ? "erro" : "success");
                    let message = resp.info.message ?? (resp.info.error ? "erro" : "ok");
                    showmessage.innerHTML = `<span class='${spanClass}'>${message}</span>`;
                }
            }).catch(() => null)
    });
});

energize.core.register("div[data-fragment]:not([energized])", (el) => {
    energize.fragment(
        el.dataset.fragment,
        el.dataset.target ? document.getElementById(el.dataset.target) : el,
        el.dataset.mode
    );
})

energize.core.register("[href][data-fragment][data-target]", (el) => {
    el.addEventListener("click", (ev) => {
        ev.preventDefault();
        energize.fragment(
            el.dataset.fragment,
            el.dataset.target ? document.getElementById(el.dataset.target) : el,
            el.dataset.mode
        );
    });
})

energize.core.register("[href]:not([href=''])", (el) => {
    let url = new URL(el.href ?? el.getAttribute('href'), document.baseURI).href;
    if (url.startsWith(window.location.href)) {
        el.classList.add('active-link')
        if (url == window.location.href)
            el.classList.add('current-link')
    } else {
        el.classList.remove('active-link')
        el.classList.remove('current-link')
    }
})