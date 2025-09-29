
window.WebRTC = {
	mediaConfig : {
		audio : true,
		video : true,
		/*
		video : {
			mandatory: {
				minWidth : 1920,
			},
		},
		//*/
	},
	rtcConfig : {
		iceServers : [
			{urls : ["stun:stun.l.google.com:19302", "stun:stunserver.org"] },
			/*
			{url:'stun:stun01.sipphone.com'},
			{url:'stun:stun.ekiga.net'},
			{url:'stun:stun.fwdnet.net'},
			{url:'stun:stun.ideasip.com'},
			{url:'stun:stun.iptel.org'},
			{url:'stun:stun.rixtelecom.se'},
			{url:'stun:stun.schlund.de'},
			{url:'stun:stun.l.google.com:19302'},
			{url:'stun:stun1.l.google.com:19302'},
			{url:'stun:stun2.l.google.com:19302'},
			{url:'stun:stun3.l.google.com:19302'},
			{url:'stun:stun4.l.google.com:19302'},
			{url:'stun:stunserver.org'},
			{url:'stun:stun.softjoys.com'},
			{url:'stun:stun.voiparound.com'},
			{url:'stun:stun.voipbuster.com'},
			{url:'stun:stun.voipstunt.com'},
			{url:'stun:stun.voxgratia.org'},
			{url:'stun:stun.xten.com'},
			//*/
		]
	},
	sseURI : "/getData.php/sse/server.vct",
	roomId : undefined,
	rootNode : undefined,
	userNode : undefined,
	videoNode : undefined,
	logNode : undefined,
	localUser : undefined,
	localStream : undefined,
	userList : {},
	sse : undefined,
	init : function() {
		this.rootNode = document.getElementById("WebRTC");
		this.userNode = document.getElementById("WebRTC-user");
		this.videoNode = document.getElementById("WebRTC-video");
		this.logNode = document.getElementById("WebRTC-log");
		this.roomId = this.rootNode.getAttribute("data-vct-room");
		
		this.initSignaling();
	},
	initSignaling : function() {
		//this.sse = new EventSource("/getData.php/webrtc/sse.pull");
		try {
			this.sse = new SSE.Client(
				this.sseURI,
				"webrtc-" + this.roomId,
				this.events.sseLog
			);
			this.sse.addEventListener("message", this.events.sseMessage);
			this.sse.addEventListener("start", this.events.sseStart);
			this.sse.addEventListener("ping", this.events.ssePing);
			this.sse.addEventListener("pong", this.events.ssePong);
			this.sse.addEventListener("call", this.events.sseCall);
			this.sse.addEventListener("ice", this.events.sseICE);
			this.sse.addEventListener("abort", this.events.sseAbort);
		} catch (e) {
			this.events.logError(e);
		}
	},
	initUser : function(data) {
		try {
			if (this.sse.userId) {
				this.userRemove(this.sse.userId);
			}
			
			this.sse.userId = data.userId;
			this.sse.userName = data.userName;
			this.sse.lastId = data.lastId;
			
			this.localUser = this.userCreate(this.sse.userId, this.sse.userName);
			this.sse.dispatchEvent("ping", JSON.stringify({userId : this.sse.userId, userName : this.sse.userName}));
		} catch (e) {
			this.events.logError(e);
		}
	},
	initMedia : function() {
		try {
			/*
			navigator.getUserMedia(
				this.mediaConfig,
				this.events.mediaStart,
				this.events.logError
			);
			//*/
			navigator.mediaDevices.getUserMedia(this.mediaConfig)
				.then(
					(stream) => {
						this.initLocalStream(stream);
					}
				);
		} catch (e) {
			this.events.logError(e);
		}
	},
	initLocalStream : function(stream) {
		this.localStream = stream;
		if (this.localStream) {
			this.localUser.setStream(this.localStream);
		}
	},
	userJoin : function() {
		this.sse.dispatchEvent("pong", JSON.stringify({userId : this.sse.userId, userName : this.sse.userName}));
	},
	userCreate : function(userId, userName) {
		var isLocal;
		isLocal = (userId === this.sse.userId);
		if (!this.userList[userId]) {
			this.userList[userId] = new WebRTC.User(this, userId, userName, isLocal);
		}
		return this.userList[userId];
	},
	userRemove : function(userId) {
		if (this.userList[userId]) {
			this.userList[userId].destruct();
			delete this.userList[userId];
		}
	},
	userSendOffer : function(userId, sdp) {
		var call = {};
		call.caller = this.localUser.id;
		call.callee = userId;
		call.type = "offer";
		call.sdp = sdp;
		this.sse.dispatchEvent("call", JSON.stringify(call));
	},
	userSendAnswer : function(userId, sdp) {
		var call = {};
		call.caller = this.localUser.id;
		call.callee = userId;
		call.type = "answer";
		call.sdp = sdp;
		this.sse.dispatchEvent("call", JSON.stringify(call));
	},
	userReceiveCall : function(data) {
		var user, callerId, calleeId, sdp;
		callerId = data.caller;
		calleeId = data.callee;
		sdp = data.sdp;
		if (calleeId === this.localUser.id) {
			if (user = this.userList[callerId]) {
				switch (data.type) {
					case "offer":
						user.receiveOffer(sdp);
						break;
					case "answer":
						user.receiveAnswer(sdp);
						break;
				}
			}
		}
	},
	userSendICE : function(userId, candidate) {
		var call = {};
		call.caller = this.localUser.id;
		call.callee = userId;
		call.candidate = candidate;
		this.sse.dispatchEvent("ice", JSON.stringify(call));
	},
	userReceiveICE : function(data) {
		var user, callerId, calleeId, candidate;
		callerId = data.caller;
		calleeId = data.callee;
		candidate = data.candidate;
		if (calleeId === this.localUser.id) {
			if (user = this.userList[callerId]) {
				user.receiveICE(candidate);
			}
		}
	},
	appendLog : function(message, type) {
		var node;
		node = document.createElementNS(NS.HTML, "span");
		node.setAttribute("class", type);
		node.textContent = message;
		this.logNode.appendChild(node);
	},
	events : {
		logMessage : function(eve) {
			WebRTC.appendLog(eve, "message");
		},
		logError : function(eve) {
			WebRTC.appendLog(eve, "error");
			//alert("ERROR ;_;" + "\n" + eve.name + "\n" + eve.message);
		},
		sseLog : function(message, type) {
			WebRTC.appendLog(message, type);
		},
		sseStart : function(eve) {
			var data = JSON.parse(eve.data);
			WebRTC.initUser(data);
			//WebRTC.initMedia();
		},
		sseMessage : function(eve) {
			alert(eve.type + "\n" + eve.data);
		},
		ssePing : function(eve) {
			var data = JSON.parse(eve.data);
			WebRTC.userJoin();
			WebRTC.userCreate(data.userId, data.userName);
		},
		ssePong : function(eve) {
			var data = JSON.parse(eve.data);
			WebRTC.userCreate(data.userId, data.userName);
		},
		sseCall : function(eve) {
			WebRTC.userReceiveCall(JSON.parse(eve.data));
		},
		sseICE : function(eve) {
			WebRTC.userReceiveICE(JSON.parse(eve.data));
		},
		sseAbort : function(eve) {
			WebRTC.userRemove(eve.data);
		},
	},
};

addEventListener(
	"load",
	function(eve) {
		WebRTC.init();
	},
	false
);