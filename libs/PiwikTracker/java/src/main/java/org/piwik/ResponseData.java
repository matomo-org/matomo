package org.piwik;

import java.net.HttpCookie;
import java.net.HttpURLConnection;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.Set;

import javax.servlet.http.Cookie;

public class ResponseData {

    private Map<String, List<String>> data;

    public ResponseData(HttpURLConnection connection) {
        data = connection.getHeaderFields();
    }

    public List<Cookie> getCookies() {
        List<Cookie> cookies = new ArrayList<Cookie>();

        Set<String> keys = data.keySet();
        for (String key : keys) {
            List<String> stringData = data.get(key);
            //String headerValue = connection.getHeaderField(i);

            String value = "";

            for (String value2 : stringData) {
                value += value2;
            }

            if (key == null && value == null) {
                // No more headers
                break;
            } else if (key == null) {
                // The header value contains the server's HTTP version
            } else if (key.equals("Set-Cookie")) {
                List<HttpCookie> httpCookies = HttpCookie.parse(value);
                for (HttpCookie h : httpCookies) {
                    Cookie c = new Cookie(h.getName(), h.getValue());
                    c.setComment(h.getComment());
                    if (h.getDomain() != null) {
                        c.setDomain(h.getDomain());
                    }
                    c.setMaxAge(new Long(h.getMaxAge()).intValue());
                    c.setPath(h.getPath());
                    c.setSecure(h.getSecure());
                    c.setVersion(h.getVersion());
                    cookies.add(c);
                }
            }
            //else
            //System.out.println(key + " : " + value);
        }
        return cookies;
    }
}
