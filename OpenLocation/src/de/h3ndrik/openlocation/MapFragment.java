package de.h3ndrik.openlocation;

import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import org.osmdroid.views.MapView;

import com.actionbarsherlock.app.SherlockFragment;

public class MapFragment extends SherlockFragment {
	final static String ARG_FOCUS = "focus";
	private String mCurrentFocus = "self";
	private MapView mMapView;
	
    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, 
        Bundle savedInstanceState) {

        // If activity recreated (such as from screen rotate), restore
        // the previous focus set by onSaveInstanceState().
        // This is primarily necessary when in the two-pane layout.
        if (savedInstanceState != null) {
            mCurrentFocus = savedInstanceState.getString(ARG_FOCUS);
        }

        // Inflate the layout for this fragment
        return inflater.inflate(R.layout.fragment_map, container, false);
    }
    
    @Override
    public void onStart() {
        super.onStart();
        
        mMapView = (MapView) getActivity().findViewById(R.id.mapview);
        mMapView.setBuiltInZoomControls(true);

        // During startup, check if there are arguments passed to the fragment.
        // onStart is a good place to do this because the layout has already been
        // applied to the fragment at this point so we can safely call the method
        // below.
        Bundle args = getArguments();
        if (args != null) {
            // Set focus based on argument passed in
            updateMap(args.getString(ARG_FOCUS));
        } else if (mCurrentFocus != null) {
            // Set focus based on saved instance state defined during onCreateView
            updateMap(mCurrentFocus);
        }
    }

    public void updateMap(String focus) {
        /*MapView map = (MapView) getActivity().findViewById(R.id.map);
        map.setText("blablabla");*/
        mCurrentFocus = focus;
    }

    @Override
    public void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);

        // Save the current article selection in case we need to recreate the fragment
        outState.putString(ARG_FOCUS, mCurrentFocus);
    }

}
