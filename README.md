TYPO3 Extension: cacheinfo
==========================

Cache Info is a small extension that has two purposes:

1) Allows to set the FE User session to be sent only when the cookie
is used. Otherwise no cookie is sent, which makes it easier to use
load-balancing and proxy caching.

2) Sends special cache headers when rendering a page request.
This way, proxy caches know if they can cache a page.

Additionally, there are some debugging headers sent in order to trace
why TYPO3 does/does not cache a page.

Disabling sending a FE session cookie by default
------------------------------------------------
Simply install the extension. Within the extension manager, the 
extension allows for configuring this option. It is enabled by default.

As soon as a frontend user logs into TYPO3, the cookie is sent,
and used. If you still need to use the FE session for users that
are not logged in (e.g. in a basket functionality), you can use
the following code in your extension:

  $hookObject = GeneralUtility::makeInstance('AOE\\Cacheinfo\\Hooks\\Userauth');
  $hookObject->persistSession();

Cookies sent with every frontend page request
---------------------------------------------

### X-T3CacheInfo
This is a info header sent to inform about certain caching
mechanisms:

#### cacheContentFlag / noCacheContentFlag
Whether TSFE object has the cacheContentFlag set, meaning that
the page content is cached within TYPO3.
  see $TSFE->cacheContentFlag

#### !no_cache!
The frontend is delivered with the "no_cache" flag activated.
  see $TSFE->no_cache

#### staticCacheable
The complete frontend page is static cacheable.
  see $TSFE->isStaticCacheble()

#### ClientCache/noClientCache
If set, then also the header X-T3-Cache is set if there are no
_INT cObjects on the page and/or logged in.
  see $TSFE->isClientCachable

#### userOrGroupSet
Set if a user is logged in or a group is set in the frontend.
 see $TSFE->isUserOrGroupSet()

#### _INT
keyword followed by all the *_INT objects used on this page.
Helpful to identify what prevents fully cacheable pages.
  see $TSFE->isINTincScript() and $TSFE->config['INTincScript']
The header "X-T3Cache" is not in this case.

#### loggedin / not_loggedin / loggingin / loggingout
whether a user is currently logged in/out.


### X-T3Cache
Header used to tell proxys that they can cache (client cacheable).


### X-T3SetCookie
Tell the proxy that a cookie is set by the current request, usually
when logging in or already logged in.


### X-T3CacheTags
Tell the proxy that the following cache tags are attached to the page,
separated by |
  see $TSFE->pageCacheTags
