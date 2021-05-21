// 初始化
const app = {};

// 配置信息
app.config = {
    serverUri: '',
};

// 登录配置
app.authConfig = {
    tokenKey: 'user_auth_token',
    userKey: 'user_auth_info',
};

// 获取URL 参数
app.getQueryString = (name) =>{
    const reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    const r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
};

// 请求模块
app.request = {
    // GET请求方式
    get: (url, body) => {
        return app.request.request('GET', url, body);
    },
    // POST请求方式
    post: (url, body) => {
        return app.request.request('POST', url, body);
    },
    // 普通请求
    request: (method, url, body) => {
        return new Promise((resolve, reject) => {
            let serverUrl = app.config.serverUri + url;
            let request = app.request.createXMLHttpRequest();
            request.onreadystatechange = () => {
                if(request.readyState === 4){
                    let response = request.responseText;
                    if (request.status === 200) {
                        resolve(response);
                        return;
                    } else if(request.status === 401) {
                        app.page.login();
                    }
                    reject(JSON.parse(response));
                }
            };
            request.open(method, serverUrl);
            // 对所有请求设置请求头
            let token = app.storage.getToken();
            if (token) {
                request.setRequestHeader('Authorization', 'Bearer ' + token.token);
            }
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            request.send(body);
        });
    },
    //实例化XMLHttpRequest对象
    createXMLHttpRequest: () => {
        if (window.XMLHttpRequest) {
            return new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            return new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
};

// 储存模块
app.storage = {
    // 获取token
    getToken: () => {
        let data = localStorage.getItem(app.authConfig.tokenKey);
        return JSON.parse(data);
    },
    // 设置token
    saveToken: (token) => {
        token = JSON.stringify(token);
        localStorage.setItem(app.authConfig.tokenKey, token)
    },
    // 删除token
    removeToken: () => {
        localStorage.removeItem(app.authConfig.tokenKey)
    },
    // 获取用户
    getUser: () => {
        let data = localStorage.getItem(app.authConfig.userKey);
        return JSON.parse(data);
    },
    // 保存用户
    saveUser: (user) => {
        user = JSON.stringify(user);
        localStorage.setItem(app.authConfig.userKey, user)
    },
    // 删除用户
    removeUser: () => {
        localStorage.removeItem(app.authConfig.userKey)
    }
};

// 页面模块
app.page = {
    // 去过的页面
    backPages: [],
    // 加载过的页面
    loadPages: {},
    // 当前页面
    currentPage: '',
    // 默认配置
    defaultConfig: {
        // appId: 'app',
        // appHeaderStyle: '',
        // appHeader: 'common/header',
        // appFooterStyle: '',
        // appFooter: 'common/footer',
        // appMenuStyle: '',
        // appMenu: 'common/menu',
        // appContentStyle: '',
        // title: '',
        // pageTitleId: 'app-page-title',
        // pageTitle: '',
        // extension: '.html',
        // view: '/app/views/',
        // // 登录页地址
        // login: 'auth/login',
        // // 注册页地址
        // register: 'auth/register',
        // // 首页地址
        // home: 'home',
    },
    // 页面配置
    config: {},
    // 页面数据
    data: {},
    // 初始化
    initConfig: (data) => {
        app.page.data = data;
        app.page.config.title = app.page.defaultConfig.title;
        app.page.config.pageTitle = app.page.defaultConfig.pageTitle;
        app.page.config.appHeader = app.page.defaultConfig.appHeader;
        app.page.config.appFooter = app.page.defaultConfig.appFooter;
        app.page.config.appMenu = app.page.defaultConfig.appMenu;
    },
    // 打开页面
    open: async(page, data) => {
        app.tool.showLoading();
        app.page.currentPage = page;
        // 初始化
        app.page.initConfig(data);
        // 内容
        let appElement = document.getElementById(app.page.defaultConfig.appId);
        if (appElement) {
            appElement.innerHTML = '';
            // 获取头部信息
            let headerElement = null;
            if (app.page.config.appHeader) {
                let headerUrl = app.page.defaultConfig.view + app.page.config.appHeader + app.page.defaultConfig.extension;
                let headerHtml = await app.request.get(headerUrl);
                if (headerHtml) {
                    headerElement = document.createElement('app-header');
                    headerElement.style.cssText = app.page.defaultConfig.appHeaderStyle;
                    headerElement.innerHTML = headerHtml;
                    appElement.appendChild(headerElement);
                }
            }
            // 加载页面内容
            let contentUrl = app.page.defaultConfig.view + page + app.page.defaultConfig.extension;
            let contentHtml = await app.request.get(contentUrl);
            if (contentHtml) {
                let contentElement = document.createElement('app-content');
                contentElement.style.cssText = app.page.defaultConfig.appContentStyle;
                contentElement.innerHTML = contentHtml;
                appElement.appendChild(contentElement);
            }
            // 加载页面底部
            let footerElement = null;
            if (app.page.config.appFooter) {
                let footerUrl = app.page.defaultConfig.view + app.page.config.appFooter + app.page.defaultConfig.extension;
                let footerHtml = await app.request.get(footerUrl);
                if (footerHtml) {
                    footerElement = document.createElement('app-footer');
                    footerElement.style.cssText = app.page.defaultConfig.appFooterStyle;
                    footerElement.innerHTML = footerHtml;
                    appElement.appendChild(footerElement);
                }
            }
            // 加载页面菜单
            let menuElement = null;
            if (app.page.config.appMenu) {
                let menuUrl = app.page.defaultConfig.view + app.page.config.appMenu + app.page.defaultConfig.extension;
                let menuHtml = await app.request.get(menuUrl);
                if (menuHtml) {
                    menuElement = document.createElement('app-menu');
                    menuElement.style.cssText = app.page.defaultConfig.appMenuStyle;
                    menuElement.innerHTML = menuHtml;
                    appElement.appendChild(menuElement);
                }
            }
            // 重新加载js
            app.page.reloadScript();
            if (!app.page.config.appHeader && headerElement) {
                headerElement.remove();
            }
            if (!app.page.config.appFooter && footerElement) {
                footerElement.remove();
            }
            if (!app.page.config.appMenu && menuElement) {
                menuElement.remove();
            }
            // 加载页面标题
            if (app.page.config.title) {
                let title = document.getElementsByTagName('title');
                if (title[0]) {
                    title[0].textContent = app.page.config.title;
                }
            }
            // 加载页面标题
            if (app.page.config.pageTitle) {
                let pageTitle = document.getElementById(app.page.defaultConfig.pageTitleId);
                if (pageTitle) {
                    pageTitle.textContent = app.page.config.pageTitle;
                }
            }
            // 记录加载过的页面
            app.page.savePagesCache(page, data);
            app.tool.hideLoading();
        }
    },
    // 储存加载过得页面
    savePagesCache: (page, data) => {
        app.page.backPages.push({
            name: page,
            data: data || null,
        });
        let pages = JSON.stringify(app.page.backPages);
        localStorage.setItem('save_pages', pages);
    },
    // 获取加载过得页面
    getPagesCache: () => {
        let pages = localStorage.getItem('save_pages');
        pages = JSON.parse(pages);
        app.page.backPages = pages;
        return pages;
    },
    // 加载默认页
    defaultPage: async() => {
        let pages = app.page.getPagesCache();
        if (pages && pages.length) {
            let page = pages[pages.length - 1];
            app.page.backPages.splice(app.page.backPages.length - 1, 1);
            await app.page.open(page.name, page.data);
        } else {
            app.page.home();
        }
    },
    // 回退
    back: async() => {
        let page = app.page.backPages[app.page.backPages.length -2];
        if (page) {
            app.page.backPages.splice(app.page.backPages.length -2, 2);
            await app.page.open(page.name, page.data);
        }
    },
    // 打开登录页
    login: async(login) => {
        const channel = app.getQueryString('channel');
        if (channel && !login) {
            await app.page.register();
        } else {
            await app.page.open(app.page.defaultConfig.login);
        }
    },
    // 打开注册页
    register: async() => {
        await app.page.open(app.page.defaultConfig.register);
    },
    // 打开首页页
    home: async() => {
        app.page.backPages = [];
        await app.page.open(app.page.defaultConfig.home);
    },
    // 重新加载js
    reloadScript: () => {
        // 重新加载js
        let content = document.getElementsByTagName('app-content');
        let scripts =  content[0].getElementsByTagName('script');
        let index = 0;
        while(index < scripts.length) {
            let script = document.createElement('script');
            script.type = 'text/javascript';
            if (scripts[index].src) {
                script.src = scripts[index].src;
            } else {
                script.innerHTML = scripts[index].innerHTML;
            }
            content[0].appendChild(script);
            scripts[index].parentElement.removeChild(scripts[index]);
            index ++;
        }
    },
    // 页面加载
    loadPage: (callback) => {
        if (!app.page.loadPages[app.page.currentPage]) {
            app.page.loadPages[app.page.currentPage] = {
                callback: callback,
            };
        }
        app.page.loadPages[app.page.currentPage].callback(app.page.currentPage);
    },
};

// app 工具
app.tool = {
    loadingImage: './resource/images/loding.gif',
    showLoading: () => {
        let body = document.getElementsByTagName('body');
        let html = '<div style="position:fixed;top:0;z-index:2000;background:#fff;width:100%;height:600px;text-align:center;">';
        html += '<img alt="" style="padding: 120px 50px;width: 100%" src="' + app.tool.loadingImage + '"/>';
        html += '</div>';
        let div = document.createElement('div');
        div.id = 'loading';
        div.innerHTML = html;
        body[0].appendChild(div);
    },
    hideLoading: () => {
        let body = document.getElementsByTagName('body');
        let div = document.getElementById('loading');
        body[0].removeChild(div);
    }
};

// 认证模块
app.auth = {
    // 验证码地址
    captchaSrc: () => {
        return new Promise((resolve, reject) => {
            app.request.get('/api/auth/captcha-src').then((data) => {
                resolve(data);
            }, error => {
                reject(error);
            });
        });
    },
    // 验证码图片
    captchaImg: () => {
        return new Promise((resolve, reject) => {
            app.request.get('/api/auth/captcha-img').then((data) => {
                resolve(data);
            }, error => {
                reject(error);
            });
        });
    },
    // 登录
    login: (mobile, code) => {
        return new Promise((resolve, reject) => {
            app.tool.showLoading();
            let param = 'mobile=' + mobile + '&code=' + code;
            app.request.post('/api/auth/login', param).then((data) => {
                app.tool.hideLoading();
                data = JSON.parse(data);
                app.storage.saveToken(data);
                resolve(data);
            }, error => {
                app.tool.hideLoading();
                reject(error);
            });
        });
    },
    // 注册
    register: (mobile, code) => {
        const channel = app.getQueryString('channel');
        return new Promise((resolve, reject) => {
            app.tool.showLoading();
            let param = 'mobile=' + mobile + '&code=' + code + '&channel=' + channel;
            app.request.post('/api/auth/register', param).then((data) => {
                data = JSON.parse(data);
                app.storage.saveToken(data);
                resolve(data);
                app.tool.hideLoading();
            }, error => {
                reject(error);
                app.tool.hideLoading();
            });
        });
    },
    // 发送验证码
    sendCode: (mobile, captcha) => {
        return new Promise((resolve, reject) => {
            let param = 'mobile=' + mobile + '&captcha=' + captcha;
            app.request.post('/api/auth/send_code', param).then((data) => {
                data = JSON.parse(data);
                app.storage.saveToken(data);
                resolve(data);
            }, error => {
                reject(error);
            });
        });
    },
    // 获取当前用户
    getUser: () => {
        return new Promise((resolve, reject) => {
            app.tool.showLoading();
            let user = app.storage.getUser();
            if (user) {
                resolve(user);
                app.tool.hideLoading();
            } else {
                app.request.get('/api/auth/user').then((data) => {
                    data = JSON.parse(data);
                    app.storage.saveUser(data);
                    resolve(data);
                    app.tool.hideLoading();
                }, error => {
                    reject(error);
                    app.tool.hideLoading();
                });
            }
        });
    },
    // 退出登录
    logout: () => {
        app.storage.removeToken();
        app.storage.removeUser();
        app.page.login();
    }
};

// 产品模块
app.product = {
    // 获取首页列表
    home: () => {
        return new Promise((resolve, reject) => {
            app.tool.showLoading();
            app.request.get('/api/product/home').then((data) => {
                data = JSON.parse(data);
                resolve(data);
                app.tool.hideLoading();
            }, error => {
                reject(error);
                app.tool.hideLoading();
            });
        });
    },
    // 获取产品列表
    list: (type, page) => {
        return new Promise((resolve, reject) => {
            app.tool.showLoading();
            app.request.get('/api/product/list?' + 'type=' + type + '&page=' + page).then((data) => {
                data = JSON.parse(data);
                resolve(data);
                app.tool.hideLoading();
            }, error => {
                reject(error);
                app.tool.hideLoading();
            });
        });
    },
    // 获取产品详情
    detail: (id) => {
        return new Promise((resolve, reject) => {
            app.tool.showLoading();
            app.request.get('/api/product/' + id).then((data) => {
                data = JSON.parse(data);
                resolve(data);
                app.tool.hideLoading();
            }, error => {
                reject(error);
                app.tool.hideLoading();
            });
        });
    },
};

// 运行app
app.run = () => {
    app.auth.getUser().then((user) => {
        if (user) {
            app.page.defaultPage();
        } else {
            app.page.login();
        }
    }, (error) => {
        console.log(error);
    });
};

// app.config.serverUri = 'http://127.0.0.1:8080';
app.config.serverUri = 'http://121.5.108.167';
app.page.defaultConfig = {
    appId: 'app',
    appHeaderStyle: 'position:fixed;left:0;top:0;width:100%;height:49px;float:left;z-index:1015;',
    appHeader: 'common/header',
    appFooterStyle: 'margin-bottom:49px;width:100%;float:left;',
    appFooter: 'common/footer',
    appMenuStyle: 'position:fixed;left:0px;bottom:0px;width:100%;height:49px;float:left;z-index:1015;',
    appMenu: 'common/menu',
    appContentStyle: 'margin-top:49px;width:100%;float:left;margin-bottom:49px;',

    title: '',
    pageTitleId: 'app-page-title',
    pageTitle: '',
    extension: '.html',
    view: '/app/views/',
    // 登录页地址
    login: 'auth/login',
    // 注册页地址
    register: 'auth/register',
    // 首页地址
    home: 'home',
};

// 运行
app.run();
