import { Offcanvas } from 'bootstrap'
import ContentListener from '../../content';
import { PageContent } from '../../page';
import { CartHelper } from '../../../../../app/components/cart/updates/CartHelper';
export class AccountHistoryActions extends PageContent {
     
    public static getInstance(): AccountHistoryActions {
        const content = new Map();

        content.set('a[data-action="account:manuals"]', [{
            eventName: 'click',
            listener: AccountHistoryActions.getManuals
        } as ContentListener]);

        content.set('a[data-action="payment:modal"]', [{
            eventName: 'click',
            listener: AccountHistoryActions.getUpdatePaymentMethodModal
        } as ContentListener]);

        content.set('button[data-action="payment:update"]', [{
            eventName: 'click',
            listener: AccountHistoryActions.updatePaymentMethod
        } as ContentListener]);

        content.set('button[data-action="payment:update"]', [{
            eventName: 'click',
            listener: AccountHistoryActions.updatePaymentMethod
        } as ContentListener]);

        content.set('div[data-action="filter:page"]', [{
            eventName: 'click',
            listener: AccountHistoryActions.pagination
        } as ContentListener]);

        content.set('button[data-action="filter:submit"]', [{
            eventName: 'click',
            listener: AccountHistoryActions.submitFilters
        } as ContentListener]);

        content.set('button[data-action="filter:clear"]', [{
            eventName: 'click',
            listener: AccountHistoryActions.clearFilters
        } as ContentListener]);

        content.set('input[type="checkbox"][data-action="filter:date"]', [{
            eventName: 'click',
            listener: (evt: Event) => AccountHistoryActions.handleCheckBox(evt, 'filter:date')
        } as ContentListener]);

        content.set('input[type="checkbox"][data-action="filter:status"]', [{
            eventName: 'click',
            listener: (evt: Event) => AccountHistoryActions.handleCheckBox(evt, 'filter:status')
        } as ContentListener]);

        content.set('button[data-action="account:buyitagain"]', [{
            eventName: 'click',
            listener: AccountHistoryActions.buyItAgain
        } as ContentListener]);

        //(Sidebar) Buttons - X or Close
        content.set('button[data-sidebarclose], span[data-sidebarclose]', [{
            eventName: 'click',
            listener: (evt: Event): void => {
                Offcanvas.getOrCreateInstance(
                    document.getElementById('sidebarModal')
                ).hide();
            }
        } as ContentListener]);

        const actions = new AccountHistoryActions(content);
        actions.onPageLoad();
        return actions;
    }

    public static getDateRange(): string{
        let url = '';
        const checkboxes = document.querySelectorAll(
            'input[type="checkbox"][data-action="filter:date"]'
        );
        checkboxes.forEach((checkbox) => {
            if ((checkbox as HTMLInputElement).checked) {
                url = 'date=' + encodeURIComponent(
                    (checkbox as HTMLInputElement).value
                );
            }
        });
        return url;
    }

    public static getStatusValues(): string {
        let params = '';
        const checkedValues: string[] = [];
        const checkboxes = document.querySelectorAll(
            'input[type="checkbox"][data-action="filter:status"]'
        );

        checkboxes.forEach((checkbox) => {
            if ((checkbox as HTMLInputElement).checked) {
                checkedValues.push(
                    encodeURIComponent(
                        (checkbox as HTMLInputElement).value
                    )
                );
            }
        });
        if (checkedValues.length > 0) {
            params = 'status=';
            params += checkedValues.join('|');
        }
        return params
    }

    public static getFilterUrlParams(): string {
        let parts: string[] = [];
        let range = this.getDateRange();
        let status = this.getStatusValues();

        if (range) {
            parts.push(range);
        }
        if (status) {
            parts.push(status);
        }
        let params = parts.join('&');
        return params;
    }

    public static pagination = (evt: Event): void => {
        let url = '';
        let active = Number((evt.target as HTMLAnchorElement).dataset.active);

        if (active) {
            url = (evt.target as HTMLAnchorElement).dataset.targetPage;
           
            //Build string of params
            let params = this.getFilterUrlParams();
           
            //Add params to URL if exists
            if (params) {
                url += '&' + params;
            }
           
            //Redirect the page
            if (url) {
                window.location.href = url;
            }
        }
    }

    public static submitFilters = (evt: Event): void => {
        //Get current URL (with no pagination params)
        let url = window.location.pathname;

        //Build string of params
        let params = this.getFilterUrlParams();

        //Add params to URL if exists
        if (params) {
            url += '?' + params;
        }
        //Redirect the page
        if (url) {
            window.location.href = url;
        }        
    }

    public static clearFilters = (evt: Event): void => {
        const statusCheckboxes = document.querySelectorAll(
            'input[type="checkbox"][data-action="filter:status"]'
        );
        const dateCheckboxes = document.querySelectorAll(
            'input[type="checkbox"][data-action="filter:date"]'
        );
        statusCheckboxes.forEach((checkbox) => {
            (checkbox as HTMLInputElement).checked = false;
        });
        dateCheckboxes.forEach((checkbox) => {
            (checkbox as HTMLInputElement).checked = false;
        });
    }

    public static handleCheckBox = (evt: Event, action: string): void => {
        const clickedCheckbox = evt.target as HTMLInputElement;
        const checkboxes = document.querySelectorAll(
            'input[type="checkbox"][data-action="' + action + '"]'
        );
        checkboxes.forEach((checkbox) => {
            if (checkbox !== clickedCheckbox) {
                (checkbox as HTMLInputElement).checked = false;
            }
        });
    }

    public static buyItAgain = (evt: Event): void => {
        const btn = evt.target as HTMLButtonElement;        
        btn.dataset.spinner = "on";
        CartHelper.addSpinner();
        CartHelper.setButtonAbility(false);
       
        window.siteData.csrfToken.then((csrf) => {
            document.body.dispatchEvent(
                new CustomEvent('cart:evt', {
                    detail: {
                        action: 'cart:add',
                        csrf: csrf,
                        payload: {
                            items: [{
                                productId: Number(btn.dataset.productId),
                                quantity: 1,
                                installation: null,
                                subscriptionSelected: false,
                                subscriptionFrequency: null,
                                subscriptionQty: null,
                                localPickup: {
                                    selected: false,
                                    selectedBranchNumber: null
                                }
                            }],
                            source: 'account:history',
                        },
                    },
                })
            );
        }).catch((error) => {
            console.error(error);
        });
    }

    public static getManuals = (evt: Event): void => {
        const prodId = Number((evt.target as HTMLAnchorElement).dataset.prodId);
        const ordersId = Number(
            (document.querySelector(
                'div[data-order-id]'
            ) as HTMLInputElement).dataset.orderId
        );
        const orderStoreId = Number(
            (document.querySelector(
                'div[data-order-store-id]'
            ) as HTMLInputElement).dataset.orderStoreId
        );
        AccountHistoryActions.getAuthToken().then((authToken) => {
            document.body.dispatchEvent(
                new CustomEvent('cart:evt', {
                    detail: {
                        action: 'account:manuals',
                        csrf: authToken,
                        url: '/customers/account/details/' + ordersId,
                        payload: {
                            productId: prodId,
                            orderStoreId: orderStoreId
                        },
                    },
                })
            );
        });
    }

    public static getUpdatePaymentMethodModal = (evt: Event): void => {
        let responseDiv = document.querySelector('.response-msg') as HTMLElement;
        responseDiv.innerHTML = '';
       
        let orderId = Number(
            (document.querySelector(
                'div[data-order-id]'
            ) as HTMLInputElement).dataset.orderId
        );

        AccountHistoryActions.getAuthToken().then((authToken) => {
            document.body.dispatchEvent(
                new CustomEvent('cart:evt', {
                    detail: {
                        action: 'payment:modal',
                        csrf: authToken,
                        url: '/customers/account/details/' + orderId,
                        payload: {
                            orderId: orderId
                        },
                    },
                })
            );
        });
    }

    public static updatePaymentMethod = (evt: Event): void => {
        let orderId = Number(
            (document.querySelector(
                'div[data-order-id]'
            ) as HTMLInputElement).dataset.orderId
        );

        //To Do:  Get Form Data
        //Need to figure out if it should be a FormData object or a JSON object
        //Need to see what requrirements of Reauth process are

        AccountHistoryActions.getAuthToken().then((authToken) => {
            document.body.dispatchEvent(
                new CustomEvent('cart:evt', {
                    detail: {
                        action: 'payment:update',
                        csrf: authToken,
                        url: '/customers/account/details/' + orderId,
                        payload: {
                            orderId: orderId
                        },
                    },
                })
            );
        });
    }

    public static paymentMethodSubmitted = (evt: CustomEvent) => {
        let modal = document.getElementById('sidebarModal');
        Offcanvas.getOrCreateInstance(modal).hide();
        let msg = evt.detail.html.body;
        let targDiv = document.querySelector('.response-msg') as HTMLElement;
        targDiv.innerHTML = msg;

        let updateLink = document.querySelector(
            'a[data-action="payment:modal"]'
        ) as HTMLElement;
        if (updateLink) {
            updateLink.addEventListener(
                'click', AccountHistoryActions.getUpdatePaymentMethodModal
            );
        }
    }

    public static handleItemAdded = (evt: CustomEvent) => {
        const itemIds = evt.detail.itemIds;
        if (itemIds.length > 0) {
            document.querySelectorAll(
                'button[data-action="account:buyitagain"]'
            ).forEach(button => {
                if (itemIds[0] === parseInt(button.getAttribute('data-product-id'))) {
                    let anchor = document.createElement('a');
                    anchor.setAttribute('href', '/shopping/cart');
                    anchor.innerText = 'View In Cart';
                    button.parentNode?.replaceChild(anchor, button);                    
                }
            });
        }
    }

    public static getAuthToken = async (): Promise<string> => {
        return new Promise((resolve) => {
            if ('siteData' in window) {
                window.siteData.authToken.then((token: string) => {
                    resolve(token);
                });
            } else {
                document.body.addEventListener('site-data:ready', (evt: Event) => {
                    window.siteData.authToken.then((token: string) => {
                        resolve(token);
                    });
                }, { once: true });
            }
        });
    }

    public static handleModal = (evt: CustomEvent) => {
        const modal = document.getElementById('sidebarModal');
        Offcanvas.getOrCreateInstance(modal).show();

        const headerView = evt.detail.html.head;
        const bodyView = evt.detail.html.body;
        const header = modal.querySelector('.offcanvas-header') as HTMLElement;
        const body = modal.querySelector('.offcanvas-body') as HTMLElement;
       
        header.innerHTML = headerView;
        body.innerHTML = bodyView;
    }

    public onPageLoad(): void {
        document.body.addEventListener(
            'modal:sidebar',
            AccountHistoryActions.handleModal,
        );
        document.body.addEventListener(
            'modal:alert',
            AccountHistoryActions.paymentMethodSubmitted,
        );
        document.body.addEventListener(
            'account:itemadded',
            AccountHistoryActions.handleItemAdded,
        );
        super.onPageLoad();
    }
}