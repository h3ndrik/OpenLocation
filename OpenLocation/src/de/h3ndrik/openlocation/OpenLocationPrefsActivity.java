package de.h3ndrik.openlocation;

import de.h3ndrik.openlocation.util.Utils;
import android.app.AlarmManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.SharedPreferences.OnSharedPreferenceChangeListener;
import android.location.LocationManager;
import android.os.Bundle;
import android.os.SystemClock;
import android.preference.PreferenceActivity;
import android.widget.Toast;

public class OpenLocationPrefsActivity extends PreferenceActivity implements
		OnSharedPreferenceChangeListener {
	// private OnSharedPreferenceChangeListener listener;

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		addPreferencesFromResource(R.xml.preferences);

		// CheckBoxPreference activate = (CheckBoxPreference)
		// findPreference("activate");
		// activate.setOnPreferenceChangeListener(onPreferenceChangeListener)
	}

	@Override
	public void onResume() {
		super.onResume();
		getPreferenceScreen().getSharedPreferences()
				.registerOnSharedPreferenceChangeListener(this);

		onSharedPreferenceChanged(getPreferenceScreen().getSharedPreferences(),
				"activate");
		onSharedPreferenceChanged(getPreferenceScreen().getSharedPreferences(),
				"username");
	}

	@Override
	public void onPause() {
		super.onPause();
		getPreferenceScreen().getSharedPreferences()
				.unregisterOnSharedPreferenceChangeListener(this);
	}

	public void onSharedPreferenceChanged(SharedPreferences sharedPreferences,
			String key) {
		// Let's do something a preference value changes
		// Toast.makeText(getBaseContext(),
		// "Preference changed: "+key.toString(), Toast.LENGTH_SHORT).show();
		if (key.equals("activate")) {

			Utils.startReceiver(getBaseContext(), true);

		} else if (key.equals("username")) {
			getPreferenceScreen().findPreference("username").setSummary(
					sharedPreferences.getString("username", getResources()
							.getString(R.string.pref_username_summary)));
		}
	}

}
