package de.h3ndrik.openlocation;

import java.io.IOException;
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

import de.h3ndrik.openlocation.util.Utils;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager.NameNotFoundException;
import android.database.Cursor;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.AsyncTask;
import android.util.Base64;
import android.util.Log;
import android.widget.Toast;

public class UpdateReceiver extends BroadcastReceiver {
	private static final String DEBUG_TAG = "UpdateReceiver"; // for logging purposes
	
	@Override
	public void onReceive(Context context, Intent intent) {
		
		Log.d(DEBUG_TAG, "got a Broadcast");
		
		if (intent.getAction() != null && intent.getAction().equals("de.h3ndrik.openlocation.update")) {
			Log.d(DEBUG_TAG, "Update requested (from Alarm)");
			
			DBAdapter db = new DBAdapter(context);
			db.dbhelper.open_r();

			if (db.dbhelper.lastUpdateMillis() < System.currentTimeMillis() - 15 * 60 * 1000) {
				LocationReceiver.doActiveUpdate(context, true);
			}
			else {
				Log.d(DEBUG_TAG, "Last update " + (System.currentTimeMillis() - db.dbhelper.lastUpdateMillis())/1000 + "sec ago, skipping active location update");
			}
			db.dbhelper.close();
			
			AsyncHttpTransfer asyncHttp = new AsyncHttpTransfer();
			asyncHttp.init(context);
			if (asyncHttp.getStatus() != AsyncTask.Status.RUNNING) {
				asyncHttp.execute();
			}
			else {
				Log.d(DEBUG_TAG, "AsyncHttpTransfer already running!");
			}
			// TODO: Maybe move sendToServer() before database work to conserve battery
			// as we got called by an inexact alarm which (hopefully) triggers when data connection
			// is active anyway. hence we should use the connection asap. Ref says radio stays alive for 6/20 sec after each request! 
		}
		else if (intent.getAction() != null && intent.getAction().equals(ConnectivityManager.CONNECTIVITY_ACTION)) {
			Log.d(DEBUG_TAG, "Network change");
			ConnectivityManager connectivityManager = (ConnectivityManager)context.getSystemService(Context.CONNECTIVITY_SERVICE);
			// intent.getBooleanExtra(ConnectivityManager.EXTRA_NO_CONNECTIVITY, defaultValue)
			if (connectivityManager.getActiveNetworkInfo() != null && connectivityManager.getActiveNetworkInfo().isConnected()) {
				/* send */
				AsyncHttpTransfer asyncHttp = new AsyncHttpTransfer();
				asyncHttp.init(context);
				if (asyncHttp.getStatus() != AsyncTask.Status.RUNNING) {
					asyncHttp.execute();
				}
				else {
					Log.d(DEBUG_TAG, "AsyncHttpTransfer already running!");
				}
			}
		}
		else {
			Log.d(DEBUG_TAG, "Not sure who sent this Broadcast");
		}

	}


	private class AsyncHttpTransfer extends AsyncTask<String, Void, String> {
		private Context context;
	
		public void init(Context arg_context) {
			context = arg_context;
		}
	
		@Override
		protected String doInBackground(String... params) {
			
			/* Check if username configured */
			if (Utils.getUsername(context) == null) {
				Log.d(DEBUG_TAG,
						"AsyncHttp: username not set");
				//Toast.makeText(
				//		context,
				//		context.getResources().getString(
				//				R.string.msg_usernotconfigured), Toast.LENGTH_SHORT)
				//		.show();
				return null;
			}
			
			/* Check the network connection */
			ConnectivityManager connMgr = (ConnectivityManager) context
					.getSystemService(Context.CONNECTIVITY_SERVICE);
			NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
			if (networkInfo == null || !networkInfo.isConnected()) {
				Log.d(DEBUG_TAG, "AsyncHttp: no internet connection");
				//Toast.makeText(
				//		context,
				//		context.getResources().getString(R.string.msg_noconnection),
				//		Toast.LENGTH_SHORT).show();
				return null;
			}
			
			/* DEBUG */
			/* TelephonyManager telephonyManager = (TelephonyManager)context.getSystemService(Context.TELEPHONY_SERVICE);
			if (networkInfo.getType() == ConnectivityManager.TYPE_MOBILE && telephonyManager.getDataActivity() == TelephonyManager.DATA_ACTIVITY_DORMANT)
				Log.d(DEBUG_TAG, "Had to wake cellular data, sorry"); */
			/* LocationManager locationManager = (LocationManager) context.getSystemService(Context.LOCATION_SERVICE);
			Location location = locationManager.getLastKnownLocation(LocationManager.PASSIVE_PROVIDER);
			if (location.getTime() > db.dbhelper.lastUpdateMillis()) {
			Log.d(DEBUG_TAG, "LocationManager missed something!");
			db.dbhelper.insertLocation(location.getTime(), location.getLatitude(), location.getLongitude(), location.getAltitude(), location.getAccuracy(), location.getSpeed(), location.getBearing(), location.getProvider()); */
			/* END DEBUG */
			
			Log.d(DEBUG_TAG, "AsyncHttp: user: " + Utils.getUsername(context) + " @ " + Utils.getDomain(context));
			
			/* Do the database work */
			DBAdapter db = new DBAdapter(context);
			db.dbhelper.open_r();
			
			String deletionMarker = "";
		
			/* Check if something is in database */
			Cursor cursor = db.dbhelper.getLocalLocations();
			if (cursor == null || cursor.getCount() < 1) {
				Log.d(DEBUG_TAG, "sendToServer(): nothing in database");
				db.dbhelper.close();
				return null;
		}
			else {
				Log.d(DEBUG_TAG, "sendToServer(): got " + Integer.toString(cursor.getCount()) + " results");
				cursor.moveToFirst();
			}
		
			/* Generate JSON */
			JSONObject json = new JSONObject();
		
			try {
				json.put("request", "setlocation");
				json.put("sender", Utils.getFullUsername(context));
				try {
					json.put("version", Integer.toString(context
							.getPackageManager().getPackageInfo(
									context.getPackageName(), 0).versionCode));
				} catch (NameNotFoundException e2) {
					// continue
				}
			} catch (JSONException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
		
			JSONArray data = new JSONArray();
		
			do {
				
				JSONObject row = new JSONObject();
		
				try {
					
					row.put(DBAdapter.LocationCacheContract.COLUMN_TIME,
							Long.toString(cursor.getLong(0)));
					row.put(DBAdapter.LocationCacheContract.COLUMN_LATITUDE,
							Double.toString(cursor.getDouble(1)));
					row.put(DBAdapter.LocationCacheContract.COLUMN_LONGITUDE,
							Double.toString(cursor.getDouble(2)));
					row.put(DBAdapter.LocationCacheContract.COLUMN_ALTITUDE,
							Double.toString(cursor.getDouble(3)));
					row.put(DBAdapter.LocationCacheContract.COLUMN_ACCURACY,
							Float.toString(cursor.getFloat(4)));
					row.put(DBAdapter.LocationCacheContract.COLUMN_SPEED,
							Float.toString(cursor.getFloat(5)));
					row.put(DBAdapter.LocationCacheContract.COLUMN_BEARING,
							Float.toString(cursor.getFloat(6)));
					row.put(DBAdapter.LocationCacheContract.COLUMN_PROVIDER,
							cursor.getString(7));
				} catch (JSONException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
		
				data.put(row);
				if (!(deletionMarker.length() == 0))
					deletionMarker += ", ";
				deletionMarker += Long.toString(cursor.getLong(0));
		
			} while (cursor.moveToNext());
		
			try {
				json.put("data", data);
			} catch (JSONException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			
			db.dbhelper.close();
			
			String data_compressed = null;
			try {
				data_compressed = Base64.encodeToString(Utils.gzdeflate(json.toString().getBytes("UTF-8")), Base64.DEFAULT);
			} catch (UnsupportedEncodingException e2) {
				// TODO Auto-generated catch block
				e2.printStackTrace();
			}
			data = null;
			json = null;
			
			/* http */
			DefaultHttpClient httpclient = new DefaultHttpClient();
	
			// TODO: httpclient.setReuseStrategy()
	
			httpclient.getCredentialsProvider().setCredentials(
					new AuthScope(Utils.getDomain(context), 80),
					new UsernamePasswordCredentials(Utils.getFullUsername(context), Utils.getPassword(context)));
	
			HttpPost httppost = new HttpPost("http://" + Utils.getDomain(context) + "/api.php");
			
			//httppost.setHeader("Content-Type", "multipart/form-data");
			
			//BasicHttpParams httpparams = new BasicHttpParams();
			//httpparams.clear();
			
			//httpparams.setParameter("json", Base64.encodeToString(compressed, Base64.DEFAULT));	// Reference says HTTP POST can send "arbitrary" amounts of data
			
			//httppost.setParams(httpparams);
			
			List<NameValuePair> pairs = new ArrayList<NameValuePair>();
			pairs.add(new BasicNameValuePair("json", data_compressed));
			
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
				Log.i("Exception (ClientProtocol)", " " + e.getMessage());
				return "Error: Exception (ClientProtocol) " + e.getMessage();
			} catch (IOException e) {
				Log.i("Exception (IO)", " " + e.getMessage());
				return "Error: Exception (IO) " + e.getMessage();
			} catch (Exception e) {
				Log.i("Exception", " " + e.getMessage());
				return "Error: Exception " + e.getMessage();
			}
			
			Log.d(DEBUG_TAG, "got response " + response.getStatusLine());
			switch (response.getStatusLine().getStatusCode()) {
			case 200:
				//deletionMarker = deletionMarker;
				break;
			default:
				deletionMarker = "Error: " + response.getStatusLine();
				break;
			}
	
			/* Garbage collection */
			data_compressed = null;
			
			try {
				response.getEntity().consumeContent();
			} catch (IOException e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
	
			/* mark rows in SQLite as done */
			if (deletionMarker != null && deletionMarker.length() > 0 && !deletionMarker.equals("0") && !deletionMarker.startsWith("Error")) {
				db.dbhelper.open_w();
				Log.d(DEBUG_TAG, "marking as done: " + deletionMarker);
				db.dbhelper.markDone(deletionMarker);
				db.dbhelper.close();
			}
	
			// TODO: Refresh webview?
	
			return deletionMarker;
		}
	
		@Override
		protected void onPostExecute(String result) {
			if (result != null && result.startsWith("Error"))
				Log.d(DEBUG_TAG, "result");
				//Toast.makeText(context, result, Toast.LENGTH_SHORT).show();
		}
		
	}
	
	public UpdateReceiver() {
	}
}
