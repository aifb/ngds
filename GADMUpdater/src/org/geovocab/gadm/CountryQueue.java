package org.geovocab.gadm;

import java.util.LinkedList;
import java.util.Queue;

public class CountryQueue {

	private Queue<Country> queue = new LinkedList<Country>();
	private boolean processing = false;
	
	public synchronized void addCountry(Country country){
	    queue.add(country);
	    this.notifyAll();
	}
	
	public synchronized Country getCountry(){
		Country country = queue.poll();
		if (queue.isEmpty()){
			this.notifyAll();	
		}
		
	    return country;
	}
	
	public synchronized boolean isEmpty(){
		return queue.isEmpty();
	}

	public synchronized boolean isProcessing() {
		return processing;
	}

	public synchronized void setProcessing(boolean processing) {
		this.processing = processing;
		this.notifyAll();
	}
	
}
