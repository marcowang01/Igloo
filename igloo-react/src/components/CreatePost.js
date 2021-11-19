import React, {useContext, useState} from "react";
import styled from "styled-components";
import { useHistory } from "react-router-dom";
import LikePost from "./LikePost";
import SavePost from "./SavePost";
import Comment from "./Comment";
import DeletePost from "./DeletePost";
import Modal from "./Modal";
import useInput from "../hooks/useInput";
import Avatar from "../styles/Avatar";
import {client, uploadImage} from "../utils";
import { timeSince } from "../utils";
import { MoreIcon, CommentIcon, InboxIcon } from "./Icons";
import {toast} from "react-toastify";
import Button from "../styles/Button";
import {useDropzone} from 'react-dropzone'
import NewPost from "../components/NewPost"
import { FeedContext } from "../context/FeedContext";
import DragDrop from "../components/DragDrop";

export const PostWrapper = styled.div`
  width: 800px;
  background: ${(props) => props.theme.white};
  border: 1px solid ${(props) => props.theme.white};
  margin-bottom: 1.5rem;
  
  box {
    display: flex;
    align-items: center;
  }

  .post-header-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .post-header {
    display: flex;
    align-items: center;
    padding: 1rem;
  }

  .post-header h3 {
    cursor: pointer;
  }

  .post-img {
    width: 500px;
    height: 100%;
  }

  .post-actions {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    padding-bottom: 0.2rem;
  }

  .post-actions svg:last-child {
    margin-left: auto;
  }

  svg {
    margin-right: 1rem;
  }

  .likes-caption-comments {
    padding: 1rem;
    padding-top: 0.3rem;
  }

  .username {
    padding-right: 0.3rem;
  }

  ul {
    display: flex;
    justify-content: start;
    position: relative;
    top: 3px;
    list-style-type: none;
    width: 100%;
    height: 100%;
  }

  li {
    margin-left: 1rem;
    align-items: center;
    vertical-align: center;
    height: 100%;
  }
  
  h1 {
    margin-left: 2rem;
  }
  
  span {
    width: 300px
  }

  textarea {
    height: 40px;
    width: 100%;
    font-family: "Fira Sans", sans-serif;
    font-size: 2rem;
    border: none;
    border-bottom: 1px solid ${(props) => props.theme.borderColor};
    resize: none;
  }

  @media screen and (max-width: 950px) {
    width: 100%;
    .post-img {
      width: 100%;
    }
  }
  
  @media screen and (max-width: 900px) {
    width: 480px;
    .post-img {
      width: 325px;
    }
  }
`;

const CreatePost = ({ post }) => {
    const history = useHistory();
    const [preview, setPreview] = useState("");
    const [postImage, setPostImage] = useState("");
    const { feed, setFeed } = useContext(FeedContext);
    const caption = useInput("");

    const [showModal, setShowModal] = useState(false);
    const closeModal = () => setShowModal(false);

    const handleUploadImage = (e) => {
        if (e.target.files[0]) {
            const reader = new FileReader();

            reader.onload = (e) => {
                setPreview(e.target.result);
                setShowModal(true);
            };
            reader.readAsDataURL(e.target.files[0]);

            uploadImage(e.target.files[0]).then((res) => {
                setPostImage(res.secure_url);
            });
        }
    };

    const handleSubmitPost = () => {
        if (!caption.value) {
            return toast.error("Please write something");
        }

        setShowModal(false);

        const tags = caption.value
            .split(" ")
            .filter((caption) => caption.startsWith("#"));

        const cleanedCaption = caption.value
            .split(" ")
            .filter((caption) => !caption.startsWith("#"))
            .join(" ");

        caption.setValue("");

        const newPost = {
            caption: cleanedCaption,
            files: [postImage],
            tags,
        };

        client(`/posts`, { body: newPost }).then((res) => {
            const post = res.data;
            post.isLiked = false;
            post.isSaved = false;
            post.isMine = true;
            setFeed([post, ...feed]);
            window.scrollTo(0, 0);
            toast.success("Your post has been submitted successfully");
        });
    };

    return (
        <PostWrapper>
            <box>
                <DragDrop />
                <div>
                    <h1>
                        <span className="caption bold">
                            <textarea
                                placeholder="Title"
                                value={caption.value}
                                onChange={caption.onChange}
                            />
                        </span>
                    </h1>
                    <ul>
                        <li>
                            <Button
                                secondary
                                onClick={() => toast.success("mint & post successful")}
                            >
                                mint & post
                            </Button>
                        </li>
                        <li>
                            <Button
                                secondary
                                onClick={() => toast.success("post successful")}
                            >
                                post
                            </Button>
                        </li>
                    </ul>
                </div>
            </box>
        </PostWrapper>
    );
};

export default CreatePost;
