/**
 * Piwik - Open source web analytics
 * 
 * @license released under BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version $Id$
 * @link http://piwik.org/docs/tracking-api/
 *
 * @category Piwik
 * @package PiwikTracker
 */
package org.piwik;

import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLEncoder;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.EnumMap;
import java.util.GregorianCalendar;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.Random;
import java.util.UUID;

import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;
import org.json.JSONArray;

/**
 * Piwik - Open source web analytics
 * 
 * Client to record visits, page views, Goals, in a Piwik server.
 * For more information, see http://piwik.org/docs/tracking-api/
 * 
 * released under BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version PiwikTracker.java 2011-03-11
 * @link http://piwik.org/docs/tracking-api/
 *
 * 
 * Changes:
 *  - coding style fixes
 *  - converted from spaces to tabs
 *  - cookiesupport removed cause was not used
 *  - more checkstyle
 *  - the url will not have empty parameters
 *  - Java 1.5 needed
 *  - logging with commons-logging
 *
 * @author Martin Fochler, Klaus Pfeiffer, Bernhard Friedreich
 * @version 1.0.5
 */
public class SimplePiwikTracker implements IPiwikTracker {

	/**
	 * Random object used for the request URl.
	 */
	private Random random = new Random(new Date().getTime());

	/**
	 * Our logger.
	 */
	private static final Log LOG = LogFactory.getLog(SimplePiwikTracker.class);

	public static final int VERSION = 1;

	/**
	 * Maximum number of custom variables.
	 */
	public static final int MAX_CUSTOM_VARIABLES = 5;

	/**
	 * Maximum length of a custom variable.
	 */
	public static final int MAX_CUSTOM_VARIABLE_LENGTH = 100;

	/**
	 * API URL.
	 */
	private URL apiurl;

	/**
	 *  Debug only.
	 */
	private String debugAppendUrl = "";

	/** 
	 * has to be set in the Request to the server 'HTTP_USER_AGENT'.
	 */
	private String userAgent;

	/**
	 * has to be set in the request to the server 'HTTP_ACCEPT_LANGUAGE'.
	 */
	private String language;

	private Date localTime;

	private Map<EBrowserPlugins, Boolean> plugins = new EnumMap<EBrowserPlugins, Boolean>(EBrowserPlugins.class);

	/**
	 * Custom data per pageview.
	 */
	private Map<String, String> pageCustomVar = new HashMap<String, String>(SimplePiwikTracker.MAX_CUSTOM_VARIABLES);

	/**
	 * Custom data per visitor.
	 */
	private Map<String, String> visitorCustomVar = new HashMap<String, String>(SimplePiwikTracker.MAX_CUSTOM_VARIABLES);

	private Date forcedDatetime;

	private String tokenAuth;

	private Cookie requestCookie;

	private String visitorId;

	/**
	 * the screen width as an int value.
	 */
	private int width;

	/**
	 * the screen height as an int value.
	 */
	private int height;

	/**
	 * If the page custom variables map is empty use this data.
	 */
	private String pageCustomData;

	/**
	 * If the visitor custom variables map is empty use this data.
	 */
	private String visitorCustomData;

	private int idSite;

	private String pageUrl;

	private String ip;

	private URL urlReferer;

	/**
	 * 
	 * @param apiUrl 
	 * @throws PiwikException 
	 */
	public SimplePiwikTracker(final String apiUrl) throws PiwikException {
		this.setApiurl(apiUrl);
		this.setVisitorId(this.md5(UUID.randomUUID().toString()).substring(0, 16));
	}

	/**
	 * Builds a PiwikTracker object, used to track visits, pages and Goal conversions 
	 * for a specific website, by using the Piwik Tracking API.
	 * 
	 * @param idSite Id of the site to be tracked
	 * @param apiUrl points to URL of the tracker server
	 * @param request 
	 * @throws PiwikException 
	 */
	public SimplePiwikTracker(final int idSite, final String apiUrl, final HttpServletRequest request)
		throws PiwikException {
		this(apiUrl);
		this.idSite = idSite;
		this.readRequestInfos(request);
	}

	/**
	 * Returns the visitor id of this tracker object.
	 * @return the visitor id as a String object
	 */
	public final String getVisitorId() {
		return this.visitorId;
	}

	/**
	 * Sets information to the tracker from the request. the information
	 * pageurl, urlreferer, useragend, ip, language and the piwik cookie will be
	 * read.
	 * 
	 * @param request 
	 * @throws PiwikException if the urls read could not be parsed to 
	 * an url object
	 */
	public final void readRequestInfos(final HttpServletRequest request) throws PiwikException {
		if (request != null) {
			this.setUrlReferer(request.getHeader("Referer"));
			this.setUserAgent(request.getHeader("User-Agent"));
			this.setPageUrl(request.getRequestURL().toString());
			this.setIp(request.getRemoteAddr());
			this.setAcceptLanguage(request.getLocale());
			if (request.getCookies() != null) {
				for (final Cookie cookie : request.getCookies()) {
					if (cookie.getName().equals("piwik_visitor")) {
						if (SimplePiwikTracker.LOG.isDebugEnabled()) {
							SimplePiwikTracker.LOG.debug("found tracking cookie");
						}
						this.setRequestCookie(cookie);
					}
				}
			}
		}
	}

	/**
	 * Sets the language set in the browser request. 
	 * This will be used to determine where the request comes from.
	 * 
	 * @param language as a string object in ISO 639 code
	 */
	public final void setAcceptLanguage(final String language) {
		this.language = language;
	}

	/**
	 * Sets the language set in the browser request. 
	 * This will be used to determine where the request comes from.
	 * 
	 * @param locale as a locale object
	 */
	public final void setAcceptLanguage(final Locale locale) {
		String localeLanguage = null;
		if (locale != null) {
			localeLanguage = locale.getLanguage();
		}
		this.setAcceptLanguage(localeLanguage);
	}

	/**
	 * Sets the url of the piwik installation the tracker will track to.
	 * 
	 * The given string should be in the format of RFC2396. The string will be
	 * converted to an url with no other url as its context. If this is not 
	 * wanted, create an own url object and use the equivalent function to this.
	 * 
	 * @param apiurl as a string object
	 * @throws PiwikException 
	 */
	public final void setApiurl(final String apiurl) throws PiwikException {
		try {
			this.setApiurl(new URL(apiurl));
		} catch (final MalformedURLException e) {
			throw new PiwikException("Could not parse given url: " + apiurl, e);
		}
	}

	/**
	 * Sets the url of the piwik installation the tracker will track to.
	 * 
	 * @param apiurl as a URL object
	 * @throws PiwikException 
	 */
	public final void setApiurl(final URL apiurl) throws PiwikException {
		if (apiurl == null) {
			throw new PiwikException("You must provide the Piwik Tracker URL! e.g. http://your-website.org/piwik/\"");
		}
		if (apiurl.getPath().endsWith("piwik.php") || apiurl.getPath().endsWith("piwik-proxy.php")) {
			this.apiurl = apiurl;
		} else {
			try {
				this.apiurl = new URL(apiurl, apiurl.getPath() + "/piwik.php");
			} catch (final MalformedURLException e) {
				// should not be thrown
				LOG.warn("MalformedURLException", e);
			}
		}
	}

	/**
	 * Sets the custom data.
	 * @param customData the data as a string object
	 */
	public final void setCustomData(final String customData) {
		this.pageCustomData = customData;
	}

	/**
	 * Sets a string for debugging usage. Please only call this function if
	 * debugging is wanted.
	 * @param debugAppendUrl 
	 */
	public final void setDebugAppendUrl(final String debugAppendUrl) {
		this.debugAppendUrl = debugAppendUrl == null ? "" : debugAppendUrl;
	}

	/**
	 * Sets the time the request was send.
	 * 
	 * @param forcedDatetime the time as a date object
	 */
	public final void setForcedDatetime(final Date forcedDatetime) {
		this.forcedDatetime = forcedDatetime;
	}

	/**
	 * Sets the ip from which the request was send.
	 * 
	 * @param ip the ip as a string object
	 */
	public final void setIp(final String ip) {
		this.ip = ip;
	}

	/**
	 * Sets the site id.
	 * @param idSite  
	 */
	public final void setIdSite(final int idSite) {
		this.idSite = idSite;
	}

	/**
	 * Sets the Page URL.
	 * @param pageUrl  
	 */
	public final void setPageUrl(final String pageUrl) {
		this.pageUrl = pageUrl;
	}

	/**
	 * Sets the screen resolution of the browser which sends the request.
	 * 
	 * @param width the screen width as an int value
	 * @param height the screen height as an int value
	 */
	public final void setResolution(final int width, final int height) {
		this.width = width;
		this.height = height;
	}

	/**
	 * Sets the piwik cookie of the requester. Therefor the name of the cookie
	 * has to be 'piwik_visitor'. All other cookies and null as parameter will
	 * reset the cookie.
	 * 
	 * @param requestCookie the piwik cookie as cookie object
	 * @return <code>true</code> if the cookie was set otherwise false
	 */
	public final boolean setRequestCookie(final Cookie requestCookie) {
		Cookie tobeset = null;
		if (requestCookie != null && requestCookie.getName().equals("piwik_visitor")) {
			tobeset = requestCookie;
		}
		this.requestCookie = tobeset;
		return this.requestCookie != null;
	}

	/**
	 * Sets the authentication string from the piwik installation for access 
	 * of piwik data.
	 * 
	 * @param tokenAuth the token as a string object
	 */
	public final void setTokenAuth(final String tokenAuth) {
		this.tokenAuth = tokenAuth;
	}

	/**
	 * Sets the referer url of the request. This will be used to determine where
	 * the request comes from.
	 * 
	 * The given string should be in the format of RFC2396. The string will be
	 * converted to an url with the apiurl as its context. This will makes relative
	 * urls to the apiurl possible. If this is not wanted, create an own url object
	 * and use the equivalent function to this.
	 * 
	 * @param urlReferer the referer url as a string object
	 * @throws PiwikException 
	 */
	public final void setUrlReferer(final String urlReferer) throws PiwikException {
		try {
			if (urlReferer == null) {
				this.urlReferer = null;
			} else {
				this.urlReferer = new URL(apiurl, urlReferer);
			}
		} catch (final MalformedURLException e) {
			throw new PiwikException("Could not parse referer url: " + urlReferer, e);
		}
	}

	/**
	 * Sets the referer url of the request. This will be used to determine where
	 * the request comes from.
	 * 
	 * @param urlReferer the referer url as a url object
	 */
	public final void setUrlReferer(final URL urlReferer) {
		this.urlReferer = urlReferer;
	}

	/**
	 * Sets the user agent identification of the requester. This will be used to
	 * determine with which kind of client the request was send.
	 * 
	 * @param userAgent the user agent identification as a string object
	 */
	public final void setUserAgent(final String userAgent) {
		this.userAgent = userAgent;
	}

	/**
	 * Sets the id of the requester. This will be used to determine if the requester
	 * is a returning visitor.
	 * 
	 * @param visitorId the id of the visitor as a string object
	 */
	public final void setVisitorId(final String visitorId) {
		this.visitorId = visitorId;
	}

	/**
	 * Sets page custom variables; ignoring fixed order (differs from PHP version).
	 * still the order shouldn't change anyway.
	 * 
	 * @param name Custom variable name
	 * @param value Custom variable value
	 * @return the count of the custom parameters
	 * @throws PiwikException when the maximum size of variables is reached or the name 
	 * 		or the value is longer as the maximum variable length
	 */
	public final int setPageCustomVariable(final String name, final String value) throws PiwikException {
		if (!this.pageCustomVar.containsKey(name) && this.pageCustomVar.size() >= SimplePiwikTracker.MAX_CUSTOM_VARIABLE_LENGTH) {
			throw new PiwikException("Max size of custom variables are reached. You can only put up to "
					+ SimplePiwikTracker.MAX_CUSTOM_VARIABLE_LENGTH + " custom variables to a request.");
		}

		if (name.length() > MAX_CUSTOM_VARIABLE_LENGTH) {
			throw new PiwikException("Parameter \"name\" exceeds maximum length of " + MAX_CUSTOM_VARIABLE_LENGTH
					+ ". Given length is " + name.length());
		}

		if (value.length() > MAX_CUSTOM_VARIABLE_LENGTH) {
			throw new PiwikException("Parameter \"value\" exceeds maximum length of " + MAX_CUSTOM_VARIABLE_LENGTH
					+ ". Given length is " + name.length());
		}

		this.pageCustomVar.put(name, value);
		return this.pageCustomVar.size();
	}

	/**
	 * Sets visitor custom variables; ignoring fixed order (differs from PHP version).
	 * still the order shouldn't change anyway.
	 * 
	 * @param name Custom variable name
	 * @param value Custom variable value
	 * @return the count of the custom parameters
	 * @throws PiwikException when the maximum size of variables is reached or the name 
	 * 		or the value is longer as the maximum variable length
	 */
	public final int setVisitorCustomVariable(final String name, final String value) throws PiwikException {
		if (!this.visitorCustomVar.containsKey(name) && this.visitorCustomVar.size() >= SimplePiwikTracker.MAX_CUSTOM_VARIABLE_LENGTH) {
			throw new PiwikException("Max size of custom variables are reached. You can only put up to "
					+ SimplePiwikTracker.MAX_CUSTOM_VARIABLE_LENGTH + " custom variables to a request.");
		}

		if (name.length() > MAX_CUSTOM_VARIABLE_LENGTH) {
			throw new PiwikException("Parameter \"name\" exceeds maximum length of " + MAX_CUSTOM_VARIABLE_LENGTH
					+ ". Given length is " + name.length());
		}

		if (value.length() > MAX_CUSTOM_VARIABLE_LENGTH) {
			throw new PiwikException("Parameter \"value\" exceeds maximum length of " + MAX_CUSTOM_VARIABLE_LENGTH
					+ ". Given length is " + name.length());
		}

		this.visitorCustomVar.put(name, value);
		return this.visitorCustomVar.size();
	}

	/**
	 * Resets all given custom variables.
	 */
	public final void clearCustomVariables() {
		this.pageCustomVar.clear();
	}

	/**
	 * Adds a browser plugin to the list to detected plugins. With the boolean 
	 * flag is set whether the plugin is enabled or disabled.
	 * 
	 * @param plugin the plugin which was detected
	 * @param enabled <code>true</code> is the plugin is enabled otherwise <code>false</code>
	 */
	public final void setPlugin(final EBrowserPlugins plugin, final boolean enabled) {
		this.plugins.put(plugin, enabled);
	}

	/**
	 * Resets all given browser plugins.
	 */
	public final void clearPluginList() {
		this.plugins.clear();
	}

	/**
	 * Sets local visitor time.
	 * 
	 * @param time the local time as a string object in the format HH:MM:SS
	 * @throws PiwikException 
	 */
	public final void setLocalTime(final String time) throws PiwikException {
		Date date = null;
		if (time != null) {
			try {
				date = new SimpleDateFormat("HH:mm:ss").parse(time);
			} catch (final ParseException e) {
				throw new PiwikException("Error while parsing given time '" + time + "' to a date object", e);
			}
		}
		this.setLocalTime(date);
	}

	/**
	 * Sets local visitor time. With null you can reset the time.
	 * 
	 * @param time the local time as a date object
	 */
	public final void setLocalTime(final Date time) {
		this.localTime = time;
	}

	/**
	 * Returns the uery part for the url with all parameters from all given 
	 * informations set to this tracker.
	 * This function is called in the defined url for the tacking purpose.
	 * 
	 * @return the query part for the url as string object
	 */
	public final String getGeneralQuery() {
		final URL rootURL = this.apiurl;
		final String rootQuery = rootURL.getQuery();
		final String withIdsite = this.addParameter(rootQuery, "idsite", this.idSite);
		final String withRec = this.addParameter(withIdsite, "rec", 1); // what ever this is
		final String withApiVersion = this.addParameter(withRec, "apiv", SimplePiwikTracker.VERSION);
		final String withURL = this.addParameter(withApiVersion, "url", this.pageUrl);
		final String withURLReferer = this.addParameter(withURL, "urlref", this.urlReferer);
		final String withVisitorId = this.addParameter(withURLReferer, "_id", this.visitorId);
		final String withReferer = this.addParameter(withVisitorId, "ref", this.urlReferer);
		final String withRefererForcedTimestamp = this.addParameter(withReferer, "_refts", this.forcedDatetime);
		final String withIp = this.addParameter(withRefererForcedTimestamp, "cip", this.ip);
		final String withForcedTimestamp = this.addParameter(withIp, "cdt", forcedDatetime == null ? null
				: new SimpleDateFormat("yyyyMMdd HH:mm:ssZ").format(forcedDatetime));
		final String withAuthtoken = this.addParameter(withForcedTimestamp, "token_auth", this.tokenAuth);
		String withPlugins = withAuthtoken;
		for (final Map.Entry<EBrowserPlugins, Boolean> entry : this.plugins.entrySet()) {
			withPlugins = this.addParameter(withPlugins, entry.getKey().toString(), entry.getValue());
		}
		final String withLocalTime;
		if (this.localTime == null) {
			withLocalTime = withPlugins;
		} else {
			final Calendar c = new GregorianCalendar();
			c.setTime(this.localTime);
			final String withHour = this.addParameter(withPlugins, "h", c.get(Calendar.HOUR_OF_DAY));
			final String withMinute = this.addParameter(withHour, "m", c.get(Calendar.MINUTE));
			withLocalTime = this.addParameter(withMinute, "s", c.get(Calendar.SECOND));
		}
		final String withResolution;
		if (this.width > 0 && this.height > 0) {
			withResolution = this.addParameter(withLocalTime, "res", this.width + "x" + this.height);
		} else {
			withResolution = withLocalTime;
		}
		final String withCookieInfo = this.addParameter(withResolution, "cookie", this.requestCookie != null);
		final String withVisitorCustomData = this.addParameter(withCookieInfo, "data", this.visitorCustomData);

		/* ADD VISITOR CUSTOM VARIABLES */

		final String withVisitorCustomVar;
		if (this.visitorCustomVar.isEmpty()) {
			withVisitorCustomVar = withVisitorCustomData;
		} else {
			final Map<String, List<String>> customVariables = new HashMap<String, List<String>>();
			int i = 0;
			for (final Map.Entry<String, String> entry : this.visitorCustomVar.entrySet()) {
				i++;
				final List<String> list = new ArrayList<String>();
				list.add(entry.getKey());
				list.add(entry.getValue());
				customVariables.put(Integer.toString(i), list);
			}

			final JSONArray json = new JSONArray();
			json.put(customVariables);

			// remove unnecessary parent square brackets from JSON-string 
			String jsonString = json.toString().substring(1, json.toString().length() - 1);

			// visitor custom variables: _cvar
			withVisitorCustomVar = this.addParameter(withVisitorCustomData, "_cvar", jsonString);
		}

		/* ADD PAGE CUSTOM VARIABLES */

		final String withPageCustomData = this.addParameter(withVisitorCustomVar, "data", this.pageCustomData);
		final String withPageCustomVar;
		if (this.pageCustomVar.isEmpty()) {
			withPageCustomVar = withPageCustomData;
		} else {
			final Map<String, List<String>> customVariables = new HashMap<String, List<String>>();
			int i = 0;
			for (final Map.Entry<String, String> entry : this.pageCustomVar.entrySet()) {
				i++;
				final List<String> list = new ArrayList<String>();
				list.add(entry.getKey());
				list.add(entry.getValue());
				customVariables.put(Integer.toString(i), list);
			}

			final JSONArray json = new JSONArray();
			json.put(customVariables);

			// remove unnecessary parent square brackets from JSON-string 
			String jsonString = json.toString().substring(1, json.toString().length() - 1);

			// page custom variables: cvar
			withPageCustomVar = this.addParameter(withPageCustomData, "cvar", jsonString);
		}

		final String withRand = this.addParameter(withPageCustomVar, "r", String.valueOf(random.nextDouble())
				.substring(2, 8));
		return (withRand + this.debugAppendUrl);
	}

	/**
	 * 
	 * @param queryString 
	 * @return URL
	 * @throws MalformedURLException 
	 */
	private URL makeURL(final String queryString) throws MalformedURLException {
		return new URL(this.apiurl, apiurl.getPath() + "?" + queryString);
	}

	/**
	 * 
	 * @param rootQuery 
	 * @param name 
	 * @param value 
	 * @return String
	 */
	private String addParameter(final String rootQuery, final String name, final int value) {
		return this.addParameter(rootQuery, name, String.valueOf(value), true);
	}

	/**
	 * 
	 * @param rootQuery 
	 * @param name 
	 * @param value 
	 * @return String
	 */
	private String addParameter(final String rootQuery, final String name, final URL value) {
		return this.addParameter(rootQuery, name, value == null ? null : value.toExternalForm(), true);
	}

	/**
	 * 
	 * @param rootQuery 
	 * @param name 
	 * @param value 
	 * @return String
	 */
	private String addParameter(final String rootQuery, final String name, final Date value) {
		return this.addParameter(rootQuery, name, value == null ? null : String.valueOf(value.getTime()), true);
	}

	/**
	 * 
	 * @param rootQuery 
	 * @param name 
	 * @param selection 
	 * @return String 
	 */
	private String addParameter(final String rootQuery, final String name, final boolean selection) {
		return this.addParameter(rootQuery, name, String.valueOf(selection), true);
	}

	/**
	 * See the equivalent function. Will call this function with ignoreNull set 
	 * to be <code>true</code>.
	 * 
	 * @param rootQuery the root query the new parameter will be added as string object
	 * @param name the name of the parameter as string object
	 * @param value the value ot the parameter as string object
	 * @return the new query as a result of the root query with the new parameter 
	 * and the value
	 */
	private String addParameter(final String rootQuery, final String name, final String value) {
		return this.addParameter(rootQuery, name, value, true);
	}

	/**
	 * Adds a parameter to a given query and returns the full query.
	 * If the given value is <code>null</code> the added query will be the string
	 * representation of <code>null</code> and NOT the empty string.
	 * If the given name is <code>null</code>, the value will be added as a 
	 * single parameter.
	 * Only if both name and value are <code>null</code> the function will
	 * return the root query unmodified.
	 * 
	 * @param rootQuery the root query the new parameter will be added as string object
	 * @param name the name of the parameter as string object
	 * @param value the value ot the parameter as string object
	 * @param ignoreNull <code>true</code> the hole parameter will be ignored if the value is <code>null</code>
	 * @return the new query as a result of the root query with the new parameter 
	 * and the value
	 */
	private String
			addParameter(final String rootQuery, final String name, final String value, final boolean ignoreNull) {
		final String output;
		if ((name == null && value == null && rootQuery != null && !(rootQuery.trim().isEmpty()))
				|| (value == null && ignoreNull)) {
			output = rootQuery;
		} else if (name != null && rootQuery != null && !rootQuery.trim().isEmpty() && value != null) {
			output = rootQuery + "&" + name + "=" + this.urlencode(value);
		} else if (rootQuery != null && !rootQuery.trim().isEmpty() && value != null) {
			output = rootQuery + "&" + this.urlencode(value);
		} else if (name != null && value != null) {
			output = name + "=" + this.urlencode(value);
		} else if (value != null) {
			output = this.urlencode(value);
		} else {
			output = rootQuery;
			LOG.error("value == null!");
		}
		return output;
	}

	/**
	 * 
	 * @param input 
	 * @return String 
	 */
	private String urlencode(final String input) {
		String output = "";
		try {
			output = URLEncoder.encode(input, "UTF-8");
		} catch (final UnsupportedEncodingException e) {
			SimplePiwikTracker.LOG.warn("Error while encoding url", e);
			output = input;
		}
		return output;
	}

	/**
	 * Creates an MD5 hash for the given input.
	 * 
	 * @param input the input string
	 * @return the hashed string 
	 */
	private String md5(final String input) {
		String retVal = "";
		try {
			final byte[] b = MessageDigest.getInstance("MD5").digest(input.getBytes());
			final java.math.BigInteger bi = new java.math.BigInteger(1, b);
			retVal = bi.toString(16);
			while (retVal.length() < 32) {
				retVal = "0" + retVal;
			}
		} catch (final NoSuchAlgorithmException e) {
			SimplePiwikTracker.LOG.error("Error while creating a md5 hash", e);
		}
		return retVal;
	}

	/**
	 * @param goal 
	 * @return URL 
	 */
	public final URL getGoalTrackURL(final String goal) {
		URL output = null;
		try {
			final String globalQuery = this.getGeneralQuery();
			final String resultQuery = this.addParameter(globalQuery, "idgoal", goal);
			output = this.makeURL(resultQuery);
		} catch (final MalformedURLException e) {
			SimplePiwikTracker.LOG.error("Error while building track url", e);
		}
		return output;
	}

	/**
	 * @param goal 
	 * @param revenue 
	 * @return URL 
	 */
	public final URL getGoalTrackURL(final String goal, final String revenue) {
		URL output = null;
		try {
			final String globalQuery = this.getGeneralQuery();
			final String qoalQuery = this.addParameter(globalQuery, "idgoal", goal);
			final String resultQuery = this.addParameter(qoalQuery, "revenue", revenue);
			output = this.makeURL(resultQuery);
		} catch (final MalformedURLException e) {
			SimplePiwikTracker.LOG.error("Error while building track url", e);
		}
		return output;
	}

	@Override
	public final URL getDownloadTrackURL(final String downloadurl) {
		URL output = null;
		try {
			final String globalQuery = this.getGeneralQuery();
			final String resultQuery = this.addParameter(globalQuery, "download", downloadurl);
			output = this.makeURL(resultQuery);
		} catch (final MalformedURLException e) {
			SimplePiwikTracker.LOG.error("Error while building track url", e);
		}
		return output;
	}

	@Override
	public final URL getLinkTrackURL(final String linkurl) {
		URL output = null;
		try {
			final String globalQuery = this.getGeneralQuery();
			final String resultQuery = this.addParameter(globalQuery, "link", linkurl);
			output = this.makeURL(resultQuery);
		} catch (final MalformedURLException e) {
			SimplePiwikTracker.LOG.error("Error while building track url", e);
		}
		return output;
	}

	@Override
	public final URL getPageTrackURL(final String pagename) {
		URL output = null;
		try {
			final String globalQuery = this.getGeneralQuery();
			final String resultQuery = this.addParameter(globalQuery, "action_name", pagename);
			output = this.makeURL(resultQuery);
		} catch (final MalformedURLException e) {
			SimplePiwikTracker.LOG.error("Error while building track url", e);
		}
		return output;
	}

	/**
	 * Sends the request to the PIWIK-Server.
	 * @param destination the built request string.
	 * @return ResponseData 
	 * @throws PiwikException 
	 */
	public final ResponseData sendRequest(final URL destination) throws PiwikException {
		ResponseData responseData = null;
		if (destination != null) {
			try {
				if (SimplePiwikTracker.LOG.isDebugEnabled()) {
					SimplePiwikTracker.LOG.debug("try to open piwik request url: " + destination);
				}
				HttpURLConnection connection = (HttpURLConnection) destination.openConnection();
				connection.setInstanceFollowRedirects(false);
				connection.setRequestMethod("GET");
				connection.setConnectTimeout(600);
				connection.setRequestProperty("User-Agent", userAgent);
				connection.setRequestProperty("Accept-Language", language);
				if (requestCookie != null) {
					connection.setRequestProperty("Cookie", requestCookie.getName() + "=" + requestCookie.getValue());
				}

				responseData = new ResponseData(connection);
				List<Cookie> cookies = responseData.getCookies();
				if (cookies.size() > 0) {
					if (cookies.get(cookies.size() - 1).getName().lastIndexOf("XDEBUG") == -1
							&& cookies.get(cookies.size() - 1).getValue().lastIndexOf("XDEBUG") == -1) {
						requestCookie = cookies.get(cookies.size() - 1);
					}
				}

				if (connection.getResponseCode() != HttpServletResponse.SC_OK) {
					SimplePiwikTracker.LOG.error("error:" + connection.getResponseCode() + " "
							+ connection.getResponseMessage());
					throw new PiwikException("error:" + connection.getResponseCode() + " "
							+ connection.getResponseMessage());
				}

				connection.disconnect();
			} catch (final IOException e) {
				throw new PiwikException("Error while sending request to piwik", e);
			}
		}
		return responseData;
	}

	/**
	 * Getter.
	 * @return custom data
	 */
	public String getCustomData() {
		return pageCustomData;
	}

	/**
	 * Getter.
	 * @return site id
	 */
	public int getIdSite() {
		return idSite;
	}

	/**
	 * Getter.
	 * @return page URL
	 */
	public String getPageUrl() {
		return pageUrl;
	}

	/**
	 * Getter.
	 * @return IP
	 */
	public String getIp() {
		return ip;
	}

	/**
	 * Getter.
	 * @return URL Referer
	 */
	public URL getUrlReferer() {
		return urlReferer;
	}

	@Deprecated
	@Override
	public URL getDownloadTackURL(final String downloadurl) {
		return getDownloadTrackURL(downloadurl);
	}

	@Deprecated
	@Override
	public URL getLinkTackURL(final String linkurl) {
		return getLinkTrackURL(linkurl);
	}

	public String getVisitorCustomData() {
		return visitorCustomData;
	}

	public void setVisitorCustomData(final String visitorCustomData) {
		this.visitorCustomData = visitorCustomData;
	}

}
