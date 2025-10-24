
export class Client {
    constructor(baseURI, name, lastId) {
        this.name = name;
        if (!lastId) {
            lastId = "";
        }
        this.lastId = lastId;
        this.pullURI = baseURI + "/pull?name=" + this.name + "&lastId=" + this.lastId;
        this.pushURI = baseURI + "/push?name=" + this.name;
        this.lastURI = baseURI + "/last?name=" + this.name;

        this.eventSource = new EventSource(this.pullURI);

        this.addEventListener(
            "error",
            (eve) => {
                console.log("ERROR while receiving event \"" + eve.type + "\" from server?!");
            }
        );
    }
    addEventListener(type, callback) {
        this.eventSource.addEventListener(
            type,
            callback,
            false
        );
    }
    dispatchEvent(type, data, callback) {
        var httpRequest, param;

        param = "";
        if (type) {
            param += "&type=" + type;
        }
        if (this.userId) {
            param += "&user=" + this.userId;
        }

        httpRequest = new XMLHttpRequest();
        httpRequest.open("POST", this.pushURI + param, true);
        httpRequest.addEventListener(
            "load",
            callback,
            false
        );

        httpRequest.send(JSON.stringify(data));
    }
    dispatchLastEvent() {
        const httpRequest = new XMLHttpRequest();
        httpRequest.open("POST", this.lastURI, true);
        httpRequest.addEventListener(
            "load",
            (eve) => {
                const newEve = new Event(httpRequest.response.type);
                newEve.data = httpRequest.response.data;
                this.eventSource.dispatchEvent(newEve);
            },
            false
        );
        httpRequest.send();
    }
}