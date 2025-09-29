// © 2014 Daniel Schulz

window.WebRTC.User = function(webRTC, id, name, isLocal) {
	this.ownerRTC = webRTC;
	this.id = id;
	this.name = name;
	this.isLocal = isLocal;
	
	this.connectionList = {};
	
	this.initHTML();
	
	if (!this.isLocal) {
		this.initConnection("offer");
		this.initConnection("answer");
	}
};


WebRTC.User.prototype.ownerRTC = undefined;
WebRTC.User.prototype.id = undefined;
WebRTC.User.prototype.name = undefined;
WebRTC.User.prototype.isLocal = undefined;
WebRTC.User.prototype.connectionList = undefined;


WebRTC.User.prototype.callButton = undefined;
WebRTC.User.prototype.receiveButton = undefined;
WebRTC.User.prototype.nameNode = undefined;
WebRTC.User.prototype.userNode = undefined;
WebRTC.User.prototype.videoNode = undefined;
WebRTC.User.prototype.initConnection = function(type) {
	if (!this.connectionList[type]) {
		var connection = new RTCPeerConnection(this.ownerRTC.rtcConfig);
		connection._ownerUser = this;
		connection._dict = {};
		switch (type) {
			case "offer":
				connection._ownerButton = this.callButton;
				connection._dict.state = {
					"new" : "send video feed to this user",
					"connecting" : "establishing connection...",
					"checking" : "establishing connection...",
					"connected" : "sending video feed to this user!!",
					"completed" : "sending video feed to this user!!",
					"failed" : "ERROR?",
					"disconnected" : "connection closed.",
					"closed" : "connection closed.",
				};
				break;
			case "answer":
				connection._ownerButton = this.receiveButton;
				connection._dict.state = {
					"new" : "not receiving video feed from this user yet",
					"connecting" : "establishing connection...",
					"checking" : "establishing connection...",
					"connected" : "receiving video feed from this user!",
					"completed" : "receiving video feed from this user!",
					"failed" : "ERROR?",
					"disconnected" : "connection closed.",
					"closed" : "connection closed.",
				};
				break;
		}
		connection._ownerButton.setAttribute("data-vct-button", type);
		connection._connectionType = type;
		connection._iceInterval = 0;
		connection._stateInterval = window.setInterval(
			function(connection) {
				var state = connection.iceConnectionState;
				if (connection.signalingState !== "stable" && connection.signalingState !== "closed") {
					state = "connecting";
				}
				connection._ownerUser.setConnectionState(connection, state);
				//var str = "iceConnectionState: "+connection.iceConnectionState + " | iceGatheringState: "+connection.iceGatheringState + " | signalingState: "+connection.signalingState;
				//console.log(str);
			},
			100,
			connection
		);
		connection.addEventListener(
			"icecandidate",
			(eve) => {
				this.sendICE(eve.candidate);
			},
			false
		);
		connection.addEventListener(
			"iceconnectionstatechange",
			(eve) => {
			},
			false
		);
			
		connection.addEventListener(
			"addstream",
			(eve) => {
				this.receiveStream(eve.stream);
			},
			false
		);
		
		if (this.ownerRTC.localStream) {
			//this.connection.addStream(this.ownerRTC.localStream);
		}
		
		this.connectionList[type] = connection;
	}
	return this.connectionList[type];
};
WebRTC.User.prototype.initHTML = function() {
	this.nameNode = document.createElementNS(NS.HTML, "span");
	this.nameNode.textContent = this.name;
	
	this.callButton = document.createElementNS(NS.HTML, "button");
	this.callButton.setAttribute("data-vct-button", "");
	if (this.isLocal) {
		this.callButton.textContent = "📹";
		this.callButton.setAttribute("title", "init own video feed");
		this.callButton.setAttribute("data-vct-connection", "new");
	} else {
		//this.callButton.textContent = "📡";
	}
	this.callButton._ownerUser = this;
	this.callButton.addEventListener(
		"click",
		function(eve) {
			this._ownerUser.callAction();
		},
		false
	);
	
	this.receiveButton = document.createElementNS(NS.HTML, "button");
	this.receiveButton.setAttribute("data-vct-button", "");
	this.receiveButton.disabled = true;
	if (this.isLocal) {
		this.receiveButton.hidden = true;
	} else {
		//this.receiveButton.textContent += "📻";
	}
	this.receiveButton._ownerUser = this;
	this.receiveButton.addEventListener(
		"click",
		function(eve) {
			this._ownerUser.receiveAction();
		},
		false
	);
	
	this.videoNode = document.createElementNS(NS.HTML, "video");
	this.videoNode.controls = true;
	this.videoNode.autoplay = true;
	if (this.isLocal) {
		this.videoNode.muted = true;
	}
	
	this.userNode = document.createElementNS(NS.HTML, "div");
	this.userNode.setAttribute("data-vct-user", this.isLocal ? "local" : "remote");
	
	this.userNode.appendChild(this.nameNode);
	this.userNode.appendChild(this.receiveButton);
	this.userNode.appendChild(this.callButton);
	
	this.ownerRTC.userNode.appendChild(this.userNode);
	/*
	if (this.isLocal) {
		this.ownerRTC.videoNode.insertBefore(
			this.callButton,
			this.ownerRTC.videoNode.firstChild
		);
	} else {
		if (this.ownerRTC.videoNode.hasChildNodes()) {
			this.ownerRTC.videoNode.insertBefore(
				this.callButton,
				this.ownerRTC.videoNode.firstChild.nextSibling
			);
		} else {
			this.ownerRTC.videoNode.appendChild(this.callButton);
		}
	}
	//*/
};
WebRTC.User.prototype.setStream = function(stream) {
	switch ("object") {
		case typeof this.videoNode.srcObject:
			this.videoNode.srcObject = stream;
			break;
		case typeof this.videoNode.mozSrcObject:
			this.videoNode.mozSrcObject = stream;
			break;
		case typeof this.videoNode.webkitSrcObject:
			this.videoNode.webkitSrcObject = stream;
			break;
		default:
			this.videoNode.src = URL.createObjectURL(stream);
			break;
	}
	if (this.isLocal) {
		this.ownerRTC.userNode.insertBefore(this.videoNode, this.ownerRTC.userNode.firstChild);
		this.callButton.disabled =  true;
		this.callButton.setAttribute("data-vct-connection", "completed");
	} else {
		this.ownerRTC.videoNode.appendChild(this.videoNode);
	}
	/*
	if (this.callButton.parentNode) {
		//this.callButton.parentNode.replaceChild(this.videoNode, this.callButton);
	}
	if (this.isLocal && this.connection) {
		//this.connection.addStream(stream);
	}
	if (this.isLocal && this.callButton.parentNode) {
		this.callButton.parentNode.replaceChild(this.videoNode, this.callButton);
	} else {
		this.ownerRTC.videoNode.appendChild(this.videoNode);
	}
	//*/
};

WebRTC.User.prototype.setConnectionState = function(connection, state) {
	if (connection._ownerButton) {
		if (connection._ownerButton.getAttribute("data-vct-connection") !== state) {
			connection._ownerButton.setAttribute("data-vct-connection", state);
			if (connection._dict.state[state]) {
				connection._ownerButton.setAttribute("title", connection._dict.state[state]);
			}
			if (state !== "new") {
				connection._ownerButton.disabled = true;
			}
			if (state === "connected") {
				//alert("remoteStream:\n"+connection.getRemoteStreams()[0].getVideoTracks());
				//+ "\n" + connection.getLocalStreams()[0]);
			}
		}
	}
};
WebRTC.User.prototype.callAction = function() {
	if (!this.isLocal && this.ownerRTC.localStream) {
		this.createOffer();
	} else {
		this.ownerRTC.initMedia();
	}
};
WebRTC.User.prototype.receiveAction = function() {
	
};
WebRTC.User.prototype.receiveICE = function(candidate) {
	var connection;
	for (var i in this.connectionList) {
		connection = this.connectionList[i];
		try {
			//connection = this.initConnection();
			connection.addIceCandidate(
				new RTCIceCandidate(candidate),
				function () {
					//alert("ICE RECEIVED\n");
				},
				this.ownerRTC.events.logError
			);
		} catch(e) {
			//öhh das muss so
			//this.ownerRTC.events.logError(e);
		}
	}
};
WebRTC.User.prototype.sendICE = function(candidate) {
	if (candidate) {
		this.ownerRTC.userSendICE(this.id, candidate);
	}
};
WebRTC.User.prototype.createOffer = function() {
	var connection;
	//this.callButton.disabled = true;
	//this.callButton.textContent = "...";

	try {
		connection = this.initConnection("offer");
		connection.addStream(this.ownerRTC.localStream);
		connection.createOffer(
			function (desc) {
				connection._ownerUser.sendOffer(desc);
			},
			this.ownerRTC.events.logError
		);
	} catch(e) {
		this.ownerRTC.events.logError(e);
	}
};
WebRTC.User.prototype.receiveOffer = function(sdp) {
	var desc, connection;
	//this.receiveButton.disabled = false;
	//this.receiveButton.textContent = "...";
	try {
		connection = this.initConnection("answer");
		desc = {};
		desc.type = "offer";
		desc.sdp = sdp;
		connection.setRemoteDescription(
			desc,
			function(eve) {
				//connection._ownerUser.createAnswer();
				
				if (!connection._iceInterval) {
					connection._iceInterval = window.setInterval(
						function(connection) {
							//console.log(connection.iceGatheringState);
							if (connection.iceGatheringState === "new") {	//chrome wtf???
								if (!connection._newCount) {
									connection._newCount = 0;
								}
								connection._newCount++;
								console.log(connection.iceGatheringState + ": " + connection._newCount);
							}
							if (connection.iceGatheringState === "complete" || connection._newCount > 10) {
								window.clearInterval(connection._iceInterval);
								connection._iceInterval = 0;
								connection._ownerUser.createAnswer();
							}
							//alert(connection.iceGatheringState);
							//connection._ownerUser.createAnswer();
						},
						100,
						connection
					);
				}
			},
			this.ownerRTC.events.logError
		);
	} catch(e) {
		this.ownerRTC.events.logError(e);
	}
};
WebRTC.User.prototype.sendOffer = function(desc) {
	var connection;
	try {
		connection = this.initConnection("offer");
		connection.setLocalDescription(desc);
		this.ownerRTC.userSendOffer(this.id, desc.sdp);
	} catch(e) {
		this.ownerRTC.events.logError(e);
	}
};
WebRTC.User.prototype.createAnswer = function() {
	var connection;
	try {
		connection = this.initConnection("answer");
		connection.createAnswer(
			function (desc) {
				connection._ownerUser.sendAnswer(desc);
			},
			this.ownerRTC.events.logError
		);
	} catch(e) {
		this.ownerRTC.events.logError(e);
	}
};
WebRTC.User.prototype.receiveAnswer = function(sdp) {
	var desc, connection;
	try {
		connection = this.initConnection("offer");
		desc = {};
		desc.type = "answer";
		desc.sdp = sdp;
		connection.setRemoteDescription(
			desc,
			function(eve) {
				//alert(connection.localDescription + "\n" + connection.remoteDescription);
				//alert("RECEIVED ANSWER OMG");
			},
			this.ownerRTC.events.logError
		);
	} catch(e) {
		this.ownerRTC.events.logError(e);
	}
};
WebRTC.User.prototype.sendAnswer = function(desc) {
	var connection;
	try {
		connection = this.initConnection("answer");
		connection.setLocalDescription(desc);
		this.ownerRTC.userSendAnswer(this.id, desc.sdp);
	} catch(e) {
		this.ownerRTC.events.logError(e);
	}
};

WebRTC.User.prototype.receiveStream = function(stream) {
	this.setStream(stream);
};

WebRTC.User.prototype.destruct = function() {
	var connection;
	for (var i in this.connectionList) {
		connection = this.connectionList[i];
		try {
			connection.close();
		} catch (e) {
			this.ownerRTC.logError(e);
		}
	}
	if (this.userNode.parentNode) {
		this.userNode.parentNode.removeChild(this.userNode);
	}
	if (this.videoNode.parentNode) {
		this.videoNode.parentNode.removeChild(this.videoNode);
	}
};