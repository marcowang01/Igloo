import React, { useEffect, useState } from "react";
import styled from "styled-components";
import { useParams } from "react-router-dom";
import PostPreview from "../components/PostPreview";
import ProfileHeader from "../components/ProfileHeader";
import Placeholder from "../components/Placeholder";
import Loader from "../components/Loader";
import { PostIcon, SavedIcon, NewPostIcon } from "../components/Icons";
import { client } from "../utils";
import RewardsPost from "../components/RewardsPost"
import CreatePost from "../components/CreatePost"
import Post from "../components/Post";
import NewPost from "../components/NewPost"
import {post1} from "../utils/FakeBackend";

const Wrapper = styled.div`
  
  .profile-tab {
    display: flex;
    align-items: center;
    justify-content: space-evenly;
    margin: 1.4rem 0;
  }

  .profile-tab div {
    display: flex;
    cursor: pointer;
    margin-right: 3rem;
  }

  .profile-tab span {
    padding-left: 0.3rem;
  }

  .profile-tab svg {
    height: 24px;
    width: 24px;
  }

  hr {
    border: 0.5px solid ${(props) => props.theme.borderColor};
  }
  
`;

const Profile = () => {
  const [tab, setTab] = useState("CREATE");

  const { username } = useParams();
  const [profile, setProfile] = useState({});
  const [loading, setLoading] = useState(true);
  const [deadend, setDeadend] = useState(false);

  useEffect(() => {
    window.scrollTo(0, 0);
    client(`/${username}`)
      .then((res) => {
        setLoading(false);
        setDeadend(false);
        setProfile(res.data);
      })
      .catch((err) => setDeadend(true));
  }, [username]);

  if (!deadend && loading) {
    return <Loader />;
  }

  if (deadend) {
    return (
      <Placeholder
        title="Sorry, this page isn't available"
        text="The link you followed may be broken, or the page may have been removed"
      />
    );
  }

  return (
    <Wrapper>
      <ProfileHeader profile={profile} />
      <hr />

      <div className="profile-tab">
        <div
          style={{ fontWeight: tab === "BLOCKS" ? "500" : "" }}
          onClick={() => setTab("BLOCKS")}
        >
          <PostIcon />
          <span>Blocks</span>
        </div>
        <div
          style={{ fontWeight: tab === "REWARDS" ? "500" : "" }}
          onClick={() => setTab("REWARDS")}
        >
          <SavedIcon />
          <span>Rewards</span>
        </div>
        {profile?.isMe &&
          <div
              style={{ fontWeight: tab === "CREATE" ? "500" : "" }}
              onClick={() => setTab("CREATE")}
          >
            <NewPostIcon />
            <span>Create</span>
          </div>
        }
      </div>

      {tab === "BLOCKS" && (
        <>
          {profile?.posts?.length === 0 ? (
            <Placeholder
              title="Blocks"
              text="Once you start making new posts, they'll appear here"
              icon="post"
            />
          ) : (
            <PostPreview posts={profile?.posts} />
          )}
        </>
      )}

      {tab === "REWARDS" && (
        <>
          {profile?.savedPosts?.length === 0 ? (
            <Placeholder
              title="Rewards"
              text="Save photos and videos that you want to see again"
              icon="bookmark"
            />
          ) : (
            <div className="rewards">
              {profile?.savedPosts?.map((post) => (
                  <RewardsPost key={post._id} post={post} />
              ))}
            </div>
          )}
        </>
      )}

      {tab === "CREATE" && (
          <>
            <CreatePost key={1}
                        post={post1} />
          </>
      )}
    </Wrapper>
  );
};

export default Profile;
