(function (window, undefined) {
  "use strict";
  //buttons
  let btn = $("#rtcUserCallButton");
  let hangupBtn = $("#hangupBtn");
  let pc;
  let sendTo = btn.data("user");
  let localStream;
  // Video Elments
  const localVideo = document.querySelector("#localVideo");
  const remoteVideo = document.querySelector("#remoteVideo");
  // media info
  const mediaConst = {
    video: true,
    audio: true,
  };
  //info about stun server
  const config = {
    iceServers: [
      {
        urls: "stun:stun1.l.google.com:19302",
      },
    ],
  };
  // what  to receive from other client
  const options = {
    offerToReceiveVideo: 1,
    offerToReceiveAudio: 1,
  };

  // connection rtc
  function getConn() {
    if (!pc) {
      pc = new RTCPeerConnection(config);
    }
  }
  //asc for media input
  async function getCam() {
    let mediaStream;
    try {
      if (!pc) {
        await getConn();
      }
      mediaStream = await navigator.mediaDevices.getUserMedia(mediaConst);
      localVideo.srcObject = mediaStream;
      localStream = mediaStream;
      localStream
        .getTracks()
        .forEach((track) => pc.addTrack(track, localStream));
    } catch (error) {
      console.log(error);
    }
  }

  async function createOffer(sendTo) {
    await sendIceCandidate(sendTo);
    await pc.createOffer(options);
    await pc.setLocalDescription(pc.localDescription);
    send("client-offer", pc.localDescription, sendTo);
  }

  async function createAnswer(sendTo, data) {
    if (!pc) {
      await getConn();
    }
    if (!localStream) {
      await getCam();
    }
    await sendIceCandidate(sendTo);
    await pc.setRemoteDescription(data);
    await pc.createAnswer();
    await pc.setLocalDescription(pc.localDescription);
    send("client-answer", pc.localDescription, sendTo);
  }

  function sendIceCandidate(sendTo) {
    pc.onicecandidate = (e) => {
      if (e.candidate !== null) {
        // send ice candidate to other client
        send("client-candidate", e.candidate, sendTo);
      }
    };
    pc.ontrack = (e) => {
      console.log(e.streams);
      remoteVideo.srcObject = e.streams[0];
    };
  }

  function hangup() {
    send("client-hangup", null, sendTo);
    pc.close();
    pc = null;
  }

  hangupBtn.on("click", () => {
    hangup();
    window.location.reload(true);
  });

  btn.on("click", () => {
    getCam();
    send("is-client-ready", null, sendTo);
  });

  conn.onopen = (e) => {
    console.log("connected to websocket");
  };

  conn.onmessage = async (e) => {
    // use data
    console.log(JSON.parse(e.data));
    let message = JSON.parse(e.data);
    let by = message.by;
    let data = message.data;
    let type = message.type;
    let image = message.image;
    let username = message.username;
    let name = message.name;
    switch (type) {
      case "client-candidate":
        if (pc.localDescription) {
          await pc.addIceCandidate(new RTCIceCandidate(data));
        }
        break;
      case "is-client-ready":
        if (!pc) {
          await getConn();
        }
        if (pc.iceConnectionState === "connected") {
          send("client-already-oncall", null, by);
        } else {
          if (window.location.href.indexOf(name) > -1) {
            if (displayCall(username)) {
              //answer
              send("client-is-ready", null, sendTo);
            }
          } else {
            if (displayCall(username)) {
              //answer
              redirectToCall(name, by);
              send("client-is-ready", null, sendTo);
            }
          }
          if (!displayCall(username)) {
            send("client-rejected", null, sendTo);
            window.location.reload(true);
          }
        }
        break;
      case "client-answer":
        if (pc.localDescription) {
          await pc.setRemoteDescription(data);
          $("#answerTimer").timer({
            format: "%m:%s",
          });
        }
        break;
      case "client-offer":
        createAnswer(sendTo, data);
        $("#answerTimer").timer({
          format: "%m:%s",
        });
        break;
      case "client-is-ready":
        createOffer(sendTo);
        break;
      case "client-already-oncall":
        setTimeout("window.location.reload(true)", 500);
        break;
      case "client-hangup":
        alert("call disconnected");
        setTimeout("window.location.reload(true)", 500);
        break;
      case "client-rejected":
        alert("client rejected the call");
        break;
    }
  };
  function send(type, data, sendTo) {
    conn.send(
      JSON.stringify({
        sendTo: sendTo,
        type: type,
        data: data,
      })
    );
  }
  function displayCall(username) {
    return confirm(
      "you have a call from : " +
        username +
        " : do you want to accept the call !!"
    );
  }
  function redirectToCall(name, sendTo) {
    console.log("redirectToCall");
    if (window.location.href.indexOf(name) == -1) {
      sessionStorage.setItem("redirect", true);
      sessionStorage.setItem("sendTo", sendTo);
      window.location.href = "/rtc/" + name;
    }
  }
  if (sessionStorage.getItem("redirect")) {
    sendTo = sessionStorage.getItem("sendTo");
    let waitForWs = setInterval(() => {
      if (conn.readyState === 1) {
        send("client-is-ready", null, sendTo);
        clearInterval(waitForWs);
      }
    }, 500);
    sessionStorage.removeItem("redirect");
    sessionStorage.removeItem("sendTo");
  }
})(window);
