import React, {useMemo, useState} from 'react';
import {useDropzone} from 'react-dropzone';
import Button from "../styles/Button";
import {uploadImage} from "../utils";
import {toast} from "react-toastify";

const baseStyle = {
    flex: 1,
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    padding: '90px',
    paddingTop: '120px',
    paddingBottom: '120px',
    borderWidth: 2,
    borderRadius: 2,
    borderColor: '#eeeeee',
    borderStyle: 'dashed',
    backgroundColor: '#fafafa',
    color: '#bdbdbd',
    outline: 'none',
    transition: 'border .24s ease-in-out',
};

const activeStyle = {
    borderColor: '#2196f3'
};

const acceptStyle = {
    borderColor: '#00e676'
};

const rejectStyle = {
    borderColor: '#ff1744'
};

const DragDrop = (props) => {
    const {
        acceptedFiles,
        getRootProps,
        getInputProps,
        isDragActive,
        isDragAccept,
        isDragReject
    } = useDropzone({
        accept: 'image/*, .jpg, .png, .doc. pdf',
        onDrop: acceptedFiles => {
            setFiles(acceptedFiles.map(file => Object.assign(file, {
                preview: URL.createObjectURL(file)
            })));
            setShowModal(true);
        }
    });

    const [showModal, setShowModal] = useState(false);
    const [files, setFiles] = useState([]);

    const style = useMemo(() => ({
        ...baseStyle,
        ...(isDragActive ? activeStyle : {}),
        ...(isDragAccept ? acceptStyle : {}),
        ...(isDragReject ? rejectStyle : {})
    }), [
        isDragActive,
        isDragReject,
        isDragAccept
    ]);

    return (
        <div className="container">
            {showModal ?
                <>
                    <img
                        className="post-preview"
                        src={files[0].preview}
                        alt="preview"
                        width="500"
                        height="100%"
                    />
                    <Button
                        secondary
                        onClick={() => {
                            setFiles([]);
                            setShowModal(false);
                        }}
                    >
                        reset
                    </Button>
                </>
                :
                <div {...getRootProps({style})}>
                    <input
                        {...getInputProps()}
                    />
                    <p>Drop or click to choose image/file</p>
                </div>
            }
        </div>
    );
};

export default DragDrop;