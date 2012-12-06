package de.h3ndrik.openlocation;

import com.actionbarsherlock.app.SherlockFragmentActivity;
import com.actionbarsherlock.view.Menu;
import com.actionbarsherlock.view.MenuItem;

import de.h3ndrik.openlocation.util.Server;
import de.h3ndrik.openlocation.util.Utils;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.os.Bundle;
import android.os.StrictMode;
import android.support.v4.app.FragmentActivity;
import android.support.v4.app.FragmentTransaction;
import android.support.v4.app.NavUtils;
import android.widget.Toast;

public class MainActivity extends SherlockFragmentActivity implements FriendsFragment.OnFriendSelectedListener {

	/** Called when the activity is first created. */
	@SuppressLint("NewApi")
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_main);
		
        // Check whether the activity is using the layout version with
        // the fragment_container FrameLayout. If so, we must add the first fragment
        if (findViewById(R.id.fragment_container) != null) {

            // However, if we're being restored from a previous state,
            // then we don't need to do anything and should return or else
            // we could end up with overlapping fragments.
            if (savedInstanceState != null) {
                return;
            }

            // Create an instance of MapFragment
            MapFragment firstFragment = new MapFragment();

            // In case this activity was started with special instructions from an Intent,
            // pass the Intent's extras to the fragment as arguments
            firstFragment.setArguments(getIntent().getExtras());

            // Add the fragment to the 'fragment_container' FrameLayout
            getSupportFragmentManager().beginTransaction()
                    .add(R.id.fragment_container, firstFragment).commit();
            
            if (android.os.Build.VERSION.SDK_INT > 9) {
                StrictMode.ThreadPolicy policy = new StrictMode.ThreadPolicy.Builder().permitAll().build();
                StrictMode.setThreadPolicy(policy);
              }
        }
	}

    public void onFriendSelected(String focus) {
        // The user selected a friend from the FriendsFragment

        // Capture the map fragment from the activity layout
        MapFragment mapFragment = (MapFragment)
                getSupportFragmentManager().findFragmentById(R.id.fragment_map);

        if (mapFragment != null) {
            // If map fragment is available, we're in two-pane layout...

            // Call a method in the MapFragment to update its content
            mapFragment.updateMap(focus);

        } else {
            // If the fragment is not available, we're in the one-pane layout and must swap fragments...

            // Create fragment and give it an argument for the selected article
            MapFragment newFragment = new MapFragment();
            Bundle args = new Bundle();
            args.putString(MapFragment.ARG_FOCUS, focus);
            newFragment.setArguments(args);
            FragmentTransaction transaction = getSupportFragmentManager().beginTransaction();

            // Replace whatever is in the fragment_container view with this fragment,
            // and add the transaction to the back stack so the user can navigate back
            transaction.replace(R.id.fragment_container, newFragment);
            transaction.addToBackStack(null);

            // Commit the transaction
            transaction.commit();
        }
    }
    
    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
    	getSupportMenuInflater().inflate(R.menu.main, menu);
    	return true;
    }
    
    @Override
	public boolean onOptionsItemSelected(MenuItem item) {
		switch (item.getItemId()) {
		case R.id.menu_friends:
	        FriendsFragment friendsFragment = (FriendsFragment)
	                getSupportFragmentManager().findFragmentById(R.id.fragment_friends);

	        if (friendsFragment != null) {
	            // friends is already there?

	        }
	        else {
	            // If the fragment is not available, we're in the one-pane layout and must swap fragments...
	            FriendsFragment newFragment = new FriendsFragment();
	            FragmentTransaction transaction = getSupportFragmentManager().beginTransaction();
	            transaction.replace(R.id.fragment_container, newFragment);
	            transaction.addToBackStack(null);
	            transaction.commit();
	        }
			break;
		case R.id.menu_settings:
			Intent intent = new Intent(MainActivity.this, PreferencesActivity.class);
			startActivity(intent);
			break;
		case R.id.menu_exit:
			Utils.stopReceiver(getBaseContext());
			Toast.makeText(getBaseContext(),
					getResources().getString(R.string.msg_updatesdisabled),
					Toast.LENGTH_SHORT).show();
			finish();
			break;
		}
		return false;
	}

}
