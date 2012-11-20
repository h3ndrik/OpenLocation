package de.h3ndrik.openlocation;

import de.h3ndrik.openlocation.util.Utils;
import android.content.SharedPreferences;
import android.content.SharedPreferences.OnSharedPreferenceChangeListener;
import android.os.Bundle;
import android.preference.PreferenceActivity;

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
		// Let's do something if a preference value changes

		if (key.equals("activate")) {
			Utils.startReceiver(getBaseContext(), true);
		} else if (key.equals("username")) {
			if (sharedPreferences.getString("username", "").equals("")) {
				getPreferenceScreen().findPreference("username").setSummary(getResources()
							.getString(R.string.pref_username_summary));
			}
			else {
				getPreferenceScreen().findPreference("username").setSummary(
						sharedPreferences.getString("username", getResources()
								.getString(R.string.pref_username_summary)));
			}
		}
		
		/* Clear webview cache if user changes */
		if (key.equals("username") || key.equals("password")) {
			OpenLocationMainActivity.clearWebviewCache(getBaseContext());
		}
	}

}
