(function(window, undefined) {
  'use strict';
  //buttons
  let btn = $('#rtcUserCallButton');
  let pc;
  let sendTo = btn.data('user');
  let localStream;
  // Video Elments
  const localVideo = document.querySelector('#localVideo')
  const remoteVideo = document.querySelector('#remoteVideo')
  // media info
  const mediaConst = {
    video: true
  };
  // connection rtc
  function getConn(){
    if (!pc){
      pc = new RTCPeerConnection();
    }
  }
  //asc for media input
  async function getCam(){
    let mediaStream;
    try {
      if (!pc){
        await getConn();
      }
      mediaStream = await navigator.mediaDevices.getUserMedia(mediaConst);
      localVideo.srcObject = mediaStream;
      localStream = mediaStream;
      localStream.getTracks().forEach(track => pc.addTrack(track, localStream));
    } catch (error) {
      console.log(error);
    }
  }
  btn.on('click', () => {
    getCam();
  });
})(window);