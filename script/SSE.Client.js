// © 2014 Daniel Schulz

SSE.Client = function(baseURI, name, logCallback, lastId) {
	this.name = name;
	this.logCallback = logCallback;
	if (!lastId) {
		lastId = "";
	}
	this.lastId = lastId;
	this.pullURI = baseURI + "?mode=pull&name=" + this.name + "&lastId=" + this.lastId;
	this.pushURI = baseURI + "?mode=push&name=" + this.name;
	this.lastURI = baseURI + "?mode=last&name=" + this.name;
	this.sseClient = this;
	
	this.loggedEvents = {};
	
	this.eventSource = new EventSource(this.pullURI);
	this.eventSource.sseClient = this;
	/*
	this.eventSource.addEventListener(
		"start",
		function(eve) {
			var data = JSON.parse(eve.data);
			this.sseClient.userId = data.userId;
			this.sseClient.userName = data.userName;
			this.sseClient.lastId = data.lastId;
		},
		false
	);
	//*/
	this.eventSource.addEventListener(
		"error",
		this.logReceiveError,
		false
	);
};
SSE.Client.prototype.name = undefined;
SSE.Client.prototype.userId = undefined;
SSE.Client.prototype.userName = undefined;
SSE.Client.prototype.lastId = undefined;
SSE.Client.prototype.pullURI = undefined;
SSE.Client.prototype.pushURI = undefined;
SSE.Client.prototype.lastURI = undefined;
SSE.Client.prototype.sseClient = undefined;
SSE.Client.prototype.eventSource = undefined;
SSE.Client.prototype.logCallback = undefined;
SSE.Client.prototype.loggedEvents = undefined;
SSE.Client.prototype.dispatchEvent = function(type, data, callback) {
	var httpRequest, param, eve;
	eve = {
		type : "message",
	};
	param = "";
	if (type) {
		param += "&type=" + type;
		eve.type = type;
	}
	if (this.userId) {
		param += "&user=" + this.userId;
	}
	httpRequest = new XMLHttpRequest();
	httpRequest.sseClient = this;
	httpRequest.successCallback = callback;
	httpRequest.eve = eve;
	httpRequest.open("POST", this.pushURI + param, true);
	httpRequest.addEventListener(
		"error",
		this.logSendError,
		false
	);
	httpRequest.addEventListener(
		"load",
		this.logSendSuccess,
		false
	);
	
	httpRequest.send(JSON.stringify(data));
};
SSE.Client.prototype.dispatchLastEvent = function() {
	var httpRequest;
	httpRequest = new XMLHttpRequest();
	httpRequest.sseClient = this;
	httpRequest.open("POST", this.lastURI, true);
	httpRequest.addEventListener(
		"error",
		this.logLastError,
		false
	);
	httpRequest.addEventListener(
		"load",
		function (eve) {
			this.sseClient.logLastSuccess(JSON.parse(this.responseText));
		},
		false
	);
	
	httpRequest.send();
};
SSE.Client.prototype.addEventListener = function(type, callback) {
	if (this.logCallback && !this.loggedEvents[type]) {
		this.loggedEvents[type] = true;
		this.eventSource.addEventListener(
			type,
			this.logReceiveSuccess,
			false
		);
	}
	this.eventSource.addEventListener(
		type,
		callback,
		false
	);
};
SSE.Client.prototype.logSendSuccess = function(eve) {
	var message = "sent event \"" + this.eve.type + "\" to server!";
	this.sseClient.logMessage(message, "message");
	if (this.successCallback) {
		this.successCallback.call(this, eve);
	}
};
SSE.Client.prototype.logSendError = function(eve) {
	var message = "failed to send event \"" + eve.type + "\" to server!";
	this.sseClient.logMessage(message, "error");
};
SSE.Client.prototype.logLastSuccess = function(eve) {
	//console.log("LAST EVENT: %o", eve);
	if (eve) {
		var newEve;
		newEve = new Event(eve.type);
		newEve.data = eve.data;
		//console.log("NEW EVENT: %o", newEve);
		this.eventSource.dispatchEvent(newEve);
	}
};
SSE.Client.prototype.logLastError = function(eve) {
	var message = "ERROR while receiving event \"" + eve.type + "\" from server?!";
	this.sseClient.logMessage(message, "error");
};
SSE.Client.prototype.logReceiveSuccess = function(eve) {
	var message = "received event \"" + eve.type + "\" from server!";
	this.sseClient.logMessage(message, "message");
};
SSE.Client.prototype.logReceiveError = function(eve) {
	var message = "ERROR while receiving event \"" + eve.type + "\" from server?!";
	this.sseClient.logMessage(message, "error");
};
SSE.Client.prototype.logMessage = function(message, type) {
	message = this.getTimestamp() + " " + message;
	if (this.logCallback) {
		this.logCallback.apply(this, [message, type]);
	}
};
SSE.Client.prototype.getTimestamp = function() {
	var date = new Date();
	return "[" + date.toLocaleTimeString() + "]";
};