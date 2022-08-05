<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="module" src="https://sdk.twilio.com/js/video/releases/2.22.1/twilio-video.min.js"></script>
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <title>Video Chat</title>
    <style>
        #local-media video, 
        #remote-media video{
            max-width: 100%;
        }
    </style>
</head>
<body>

    <div class="d-flex align-items-center"> 
        <div class="container">
            <?php require_once('backend/token.php') ?>
            <?php if(isset($_GET['join_room'])): ?>
            <div class="row">
                <div class="col-8" >
                    <h2 class="text-left">Participants</h2>
                    
                    <div class="row" id="remote-media">
                        
                    </div>
                </div>

                <div class="col-4">
                    <h2>Preview</h2>
                    <div id="local-media">
                        <img src="https://via.placeholder.com/350" class="img-thumbnail d-none" alt="#" id="local-media-image">
                    </div>
                    <div class="controls">
                        <button type="button" class="btn btn-primary" id="toggle-mic">                        
                            <i class="bi bi-mic" id="mic-on-icon"></i>
                            <i class="bi bi-mic-mute d-none" id="mic-off-icon"></i>
                            Mic
                        </button>
                        
                        <button type="button" class="btn btn-primary" id="toggle-video">
                            <i class="bi bi-camera-video" id="video-on-icon"></i>
                            <i class="bi bi-camera-video-off d-none" id="video-off-icon"></i>
                            Camera
                        </button>
                        
                        <button type="button" class="btn btn-primary" id="join-room">
                            <i class="bi bi-box-arrow-in-right" id="join-room-icon"></i>
                            <span id="toggle-join-room-text">Join room</span>
                        </button>
                        <button type="button" class="btn btn-danger" id="disconnect">
                            <i class="bi bi-box-arrow-in-left" id="leave-room-icon"></i>
                            <span id="toggle-join-room-text">Disconnect</span>
                        </button>
                    </div>
                    <div class="notifications">
                        <h2>Notifications</h2>
                        <ul id="notifications">
    
                        </ul>
                    </div>
                    
                </div>
                
            </div>
            <?php else: ?>
            <div class="mt-5 text-center">
                <a id="join-room" class="btn btn-primary" href="?join_room=1">
                    Join <?= $roomName ?> 
                </a>
            </div>
            <?php endif; ?>
        </div>  
    </div>

    <script type="module">

        window.videoOn = true;
        window.micOn = true;
        let joinedRoom = false;

        const videoOnIcon = document.getElementById('video-on-icon');
        const videoOffIcon = document.getElementById('video-off-icon');

        const micOnIcon = document.getElementById('mic-on-icon');
        const micOffIcon = document.getElementById('mic-off-icon');

        function notify(message, n_class='text-primary', container='notifications'){
            let li = document.createElement('li');
            li.setAttribute('class', n_class);
            li.appendChild(document.createTextNode(message))
            document.getElementById(container).appendChild(li);

        }

        function updateMic(RoomOrLocalAudio, domain="room"){
            window.micOn = !window.micOn;
            alert(`Mic toggling: ${window.micOn}`)
            if(window.micOn){
                micOnIcon.classList.remove("d-none")
                micOffIcon.classList.add("d-none")

                // unmute audio
                if(domain == "room"){
                    alert(`Audio is ${window.micOn} in a ${domain}`)
                    console.log(RoomOrLocalAudio)
                    console.log(RoomOrLocalAudio)
                    handleAudioUnmute(RoomOrLocalAudio)
                }else{
                    alert(`Audio is ${window.micOn} in a ${domain}`)
                    RoomOrLocalAudio.enable()
                }
            }else{
                micOnIcon.classList.add("d-none")
                micOffIcon.classList.remove("d-none")

                // mute audio
                if(domain == "room"){
                    alert(`Audio is ${window.micOn} in a ${domain}`)
                    handleAudioMute(RoomOrLocalAudio)
                }else{
                    alert(`Audio is ${window.micOn} in a ${domain}`)
                    RoomOrLocalAudio.disable()
                }

                console.log(RoomOrLocalAudio)
            }
        }


        function updateVideo(RoomOrLocalVideo, domain='room'){
            window.videoOn = !window.videoOn;
            alert(`Video is ${window.videoOn}`)
            if(window.videoOn){
                videoOnIcon.classList.remove("d-none")
                videoOffIcon.classList.add("d-none")

                // On video
                if(domain == 'room'){
                    alert(`Video is ${window.videoOn} in a room`)
                    handleVideoUnMute(RoomOrLocalVideo)
                }else{
                    alert(`Video is ${window.videoOn} in ${domain}`)
                    RoomOrLocalVideo.enable();
                }
            }else{
                videoOnIcon.classList.add("d-none")
                videoOffIcon.classList.remove("d-none")
                        
                //off video
                if(domain == 'room'){
                    alert(`Turn off: Video is ${window.videoOn} in a room`)
                    handleVideoMute(RoomOrLocalVideo)
                }else{
                    alert(`Turn off: Video is ${window.videoOn} in a ${domain}`)
                    RoomOrLocalVideo.disable();
                }

                console.log(RoomOrLocalVideo)
            }
        }

        function handleAudioMute(room){
            room.localParticipant.audioTracks.forEach(publication => {
                publication.track.disable();
                // publication.track.detach().forEach(mediaElement => mediaElement.remove());
            });
        }
       
        function handleAudioUnmute(room){
            room.localParticipant.audioTracks.forEach(publication => {
                publication.track.enable();
            });
        }

        function handleVideoMute(room){
            room.localParticipant.videoTracks.forEach(publication => {
              publication.track.disable();
            //   publication.track.detach().forEach(mediaElement => mediaElement.remove());
            });
        }
       
        function handleVideoUnmute(room){
            room.localParticipant.videoTracks.forEach(publication => {
              publication.track.enable();
            });
        }

        function handleTrackDisabled(track) {
            track.on('disabled', () => {
                /* Hide the associated <video> element and show an avatar image. */
                alert("Video disabling")
                console.log(track)
            });
        }

        function handleRemoteMediaDiv(track, participant){
            let participantClass = `.paticipants.col-sm-4.${participant.identity}`
            let participantDiv = document.querySelector(participantClass)
            if(participantDiv){
                participantDiv.appendChild(track.attach())
            }else{
                participantDiv = document.createElement('div');
                participantDiv.setAttribute('class', `paticipants col-sm-4 ${participant.identity}`);
            }
            
            document.getElementById('remote-media').appendChild(participantDiv);
        }


        document.addEventListener("DOMContentLoaded", function(event) { 

            const toggleMic = document.getElementById('toggle-mic');
           
            const toggleVideo = document.getElementById('toggle-video');
            const localMediaImage = document.getElementById('local-media-image');
             
            const btnJoinRoom = document.getElementById('join-room');
            const btnDisconnect = document.getElementById('disconnect');
            
            const Video = Twilio.Video;   
            const twilio_token = document.getElementById('twilio_token');
            

            if(twilio_token){ 

                Video.createLocalAudioTrack().then(localAudioTrack => {
                    console.log(`Created LocalAudioTrack with id ${localAudioTrack.id}`)
                    const audioElement = localAudioTrack.attach();
                    document.body.appendChild(audioElement);
                    
                    toggleMic.addEventListener('click', function(e){
                        updateMic(localAudioTrack, 'preview');
                    })
                    
                })

                Video.createLocalVideoTrack().then(localVideoTrack => {
                  
                    console.log(`Created LocalVideoTrack with id ${localVideoTrack.id}`)
                    const localVideoContainer = document.getElementById('local-media');
                    localVideoContainer.appendChild(localVideoTrack.attach());

                    toggleVideo.addEventListener('click', function(e){
                        updateVideo(localVideoTrack, 'preview');
                    })
                    
                });
            }

            

            btnJoinRoom.addEventListener('click', function(e){
                
                    document.getElementById('twilio_token')

                    Video.connect(twilio_token.value, { 
                        name:'DailyStandup',
                    }).then(room => {
                        const localParticipant = room.localParticipant;

                        // off mic and video if disabled
                        if(window.micOn){
                  
                            // Video.createLocalAudioTrack().then(localAudioTrack => {
                            //     console.log(`Created LocalAudioTrack with id ${localAudioTrack.id}`)
                            //     const audioElement = localAudioTrack.attach();
                            //     document.body.appendChild(audioElement);
                                
                            // })

                            handleAudioUnmute(room)

                        }else{
                            handleAudioMute(room)
                        }
                        
                        if(window.videoOn){
                            handleVideoUnmute(room)
                        }else{
                            handleVideoMute(room)
                        }
                        
                        // notify others that user successfully joined the room
                        notify(`${localParticipant.identity} Successfully joined a Room: ${room}`);

                        // Loop through all participants that have joined
                        notify(`${room.participants.size} Participants are connected to the Room`)

                        room.participants.forEach(participant => {    
                            
                            participant.tracks.forEach(publication => {
                                if (publication.track) {
                                    handleRemoteMediaDiv(publication.track, participant)
                                }
                            });

                            participant.on('trackSubscribed', track => {
                                handleRemoteMediaDiv(track, participant)
                            });

                            participant.tracks.forEach(publication => {
                                if (publication.isSubscribed) {
                                    handleTrackDisabled(publication.track);
                                }
                                publication.on('subscribed', handleTrackDisabled);
                            });
                        });


                        // Display video and audio if turned on
                        // show videos of localparticipant
                        if(window.videoOn){
                            Video.createLocalVideoTrack().then(localVideoTrack => {
                                return room.localParticipant.publishTrack(localVideoTrack);
                            }).then(publication => {
                                console.log('Successfully unmuted your video:', publication);
                                if (publication.isSubscribed) {
                                    const track = publication.track;
                                    // document.getElementById('remote-media').appendChild(track.attach());
                                }
                            });
                        }
                        
                     


                        // Get new participants that connects
                        room.on('participantConnected', participant => {
                            
                            // show in notfification area
                            notify(`A remote Participant connected: ${participant.identity}`)
                            console.log(`Participant "${participant.identity}" connected`);

                            participant.tracks.forEach(publication => {
                                if (publication.isSubscribed) {
                                    handleRemoteMediaDiv(publication.track, participant)
                                }
                            });

                            participant.on('trackSubscribed', track => {
                                handleRemoteMediaDiv(track, participant)
                            });
                        });

                        // Handle disconnection from room
                        room.on('disconnected', room => {
                            // Detach the local media elements
                            room.localParticipant.tracks.forEach(publication => {
                                const attachedElements = publication.track.detach();
                                attachedElements.forEach(element => element.remove());
                            });

                        });

                        // Log Participants as they disconnect from the Room
                        room.once('participantDisconnected', participant => {
                            console.log(`Participant "${participant.identity}" has disconnected from the Room`);
                            document.querySelector(`.paticipants.col-sm-4.${participant.identity}`).remove();
                        });

                        btnDisconnect.addEventListener('click', function(e){
                            alert('disconnection');
                            console.log("leaving room");
                            room.disconnect();
                            console.log("left room");
                        })

                        toggleVideo.addEventListener('click', function(e){
                            updateVideo(room);
                        })

                          
                        toggleMic.addEventListener('click', function(e){
                            updateMic(room);
                        })
                    })

                 
                    joinedRoom = true;
                    console.log("joined room");
                    
            });
            
        });
        
    </script>
</body>
</html>