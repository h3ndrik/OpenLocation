/**
 * 
 */
package de.h3ndrik.openlocation.util;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.util.ArrayList;
import java.util.List;

import org.apache.http.HttpResponse;
import org.apache.http.NameValuePair;
import org.apache.http.auth.AuthScope;
import org.apache.http.auth.UsernamePasswordCredentials;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.content.Context;
import android.content.SharedPreferences;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.preference.PreferenceManager;
import android.util.Base64;
import android.util.Log;

/**
 * @author h3ndrik
 *
 */
public class Server {
	private static final String DEBUG_TAG = "Server"; // for logging purposes
	Context context;
	
	public static String[]	friendsArray = 
	    {
        "Henry IV (1)",   
        "Henry V",
        "Henry VIII",       
        "Richard II",
        "Richard III",
        "Merchant of Venice",  
        "Othello",
        "King Lear"
};

	public void connect() {
		throw new UnsupportedOperationException();
	}
	
	public void disconnect() {
		throw new UnsupportedOperationException();
	}
	
	public void init(Context arg_context) {
		context = arg_context;
	}
	
	public JSONObject send (JSONObject json) {
		/* Check if username configured */
		if (Utils.getUsername(context) == null) {
			Log.d(DEBUG_TAG,
					"username not set");
			return null;
		}
		
		/* Check the network connection */
		ConnectivityManager connMgr = (ConnectivityManager) context
				.getSystemService(Context.CONNECTIVITY_SERVICE);
		NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
		if (networkInfo == null || !networkInfo.isConnected()) {
			Log.d(DEBUG_TAG, "no internet connection");
			return null;
		}
		
		String json_compressed = null;
		try {
			json_compressed = Base64.encodeToString(Utils.gzdeflate(json.toString().getBytes("UTF-8")), Base64.DEFAULT);
		} catch (UnsupportedEncodingException e2) {
			// TODO Auto-generated catch block
			e2.printStackTrace();
		}
		
		/* http */
		DefaultHttpClient httpclient = new DefaultHttpClient();
		// TODO: httpclient.setReuseStrategy()
		httpclient.getCredentialsProvider().setCredentials(
				new AuthScope(Utils.getDomain(context), 80),
				new UsernamePasswordCredentials(Utils.getFullUsername(context), Utils.getPassword(context)));
		HttpPost httppost = new HttpPost("http://" + Utils.getDomain(context) + "/api.php");
		List<NameValuePair> pairs = new ArrayList<NameValuePair>();
		pairs.add(new BasicNameValuePair("json", json_compressed));
		try {
			httppost.setEntity(new UrlEncodedFormEntity(pairs));
			// httppost.setEntity(new StringEntity(json.toString()));
		} catch (UnsupportedEncodingException e1) {
			// TODO Auto-generated catch block
			e1.printStackTrace();
		}

		Log.d(DEBUG_TAG, "executing request " + httppost.getRequestLine());
		HttpResponse response = null;
		try {
			response = httpclient.execute(httppost);
		} catch (ClientProtocolException e) {
			Log.i(DEBUG_TAG, "Exception (ClientProtocol): " + e.getMessage());
			return null;
		} catch (IOException e) {
			Log.i(DEBUG_TAG, "Exception (IO): " + e.getMessage());
			return null;
		} catch (Exception e) {
			Log.i(DEBUG_TAG, "Exception: " + e.getMessage());
			return null;
		}
		
		StringBuilder builder = new StringBuilder();
		Log.d(DEBUG_TAG, "got response " + response.getStatusLine());
		if (response.getStatusLine().getStatusCode() == 200) {
			InputStream content;
			try {
				content = response.getEntity().getContent();
				BufferedReader reader = new BufferedReader(new InputStreamReader(content));
				String line = null;
				while ((line = reader.readLine()) != null)
					builder.append(line);
			}
			catch (IllegalStateException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			catch (IOException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
		}
		else {
			return null; // "Error, wrong response " + Integer.toString(response.getStatusLine().getStatusCode()) + " from server";
		}
		
		JSONObject result = null;
		try {
			result = new JSONObject(new String(Utils.gzinflate(Base64.decode(builder.toString(), Base64.DEFAULT)), "UTF-8"));
		}
		catch (JSONException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		catch (UnsupportedEncodingException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

		return result;
	}
	
	public class API {
		public void getLocation(String target, Long starttime, Long endtime) throws Exception {
			throw new UnsupportedOperationException();
			// get sender
			// get token
			// get target

		}
		public void setLocation() {
			throw new UnsupportedOperationException();

		}
		public void deleteLocation() {
			throw new UnsupportedOperationException();

		}
		public String getFriends() {
			JSONObject request = new JSONObject();
			try {
				request.put("request", "getfriends");
				request.put("sender", Utils.getUsername(context));
				request.put("token", getToken());
				JSONObject result = send(request);
				JSONArray friends = result.getJSONArray("data");
				JSONArray pending = result.getJSONArray("pending");
				JSONArray incoming = result.getJSONArray("incoming");
					return friends.join(",");
			}
			catch (JSONException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			return null;
		}
		public void requestFriend() {
			throw new UnsupportedOperationException();
			// This is sendrequestfriend!
		}
		public void removeFriend() {
			throw new UnsupportedOperationException();

		}
		public void updatePreferences() {
			throw new UnsupportedOperationException();
			
		}
		public String getToken() {
			SharedPreferences SP = PreferenceManager.getDefaultSharedPreferences(context);
			String pref_token = SP.getString("token", null);
			if (pref_token != null && pref_token.length() == 32)
				return pref_token;
			
			JSONObject request = new JSONObject();
			try {
				request.put("request", "getuserpreferences");
				request.put("sender", Utils.getUsername(context));
				JSONObject result = send(request);
				if (result == null)
					return null;
				String token = result.getString("token");
				if (token.length() == 32)
					SP.edit().putString("token", token);
					return token;
			}
			catch (JSONException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
				return null;
		}
	}


}
