import React, { ChangeEvent, useEffect, useState } from 'react';
import { httpErrorToHuman } from '@/api/http';
import { CSSTransition } from 'react-transition-group';
import Spinner from '@/components/elements/Spinner';
import FileObjectRow from '@/components/server/files/FileObjectRow';
import FileManagerBreadcrumbs from '@/components/server/files/FileManagerBreadcrumbs';
import { FileObject } from '@/api/server/files/loadDirectory';
import NewDirectoryButton from '@/components/server/files/NewDirectoryButton';
import { NavLink, useLocation } from 'react-router-dom';
import Can from '@/components/elements/Can';
import { ServerError } from '@/components/elements/ScreenBlock';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import { ServerContext } from '@/state/server';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import MassActionsBar from '@/components/server/files/MassActionsBar';
import UploadButton from '@/components/server/files/UploadButton';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import { useStoreActions, useStoreState } from '@/state/hooks';
import ErrorBoundary from '@/components/elements/ErrorBoundary';
import { FileActionCheckbox } from '@/components/server/files/SelectFileCheckbox';
import { formatIp, hashToPath } from '@/helpers';
import Input from '@/components/elements/Input';
import Label from '@/components/elements/Label';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import CopyOnClick from '@/components/elements/CopyOnClick';
import isEqual from 'react-fast-compare';

const sortFiles = (files: FileObject[], searchString: string): FileObject[] => {
    const sortedFiles: FileObject[] = files.sort((a, b) => a.name.localeCompare(b.name)).sort((a, b) => a.isFile === b.isFile ? 0 : (a.isFile ? 1 : -1));
    return sortedFiles.filter((file, index) => index === 0 || file.name !== sortedFiles[index - 1].name).filter((file) => file.name.toLowerCase().includes(searchString.toLowerCase()));
};

export default () => {
    const id = ServerContext.useStoreState(state => state.server.data!.id);
    const { hash } = useLocation();
    const { data: files, error, mutate } = useFileManagerSwr();
    const directory = ServerContext.useStoreState(state => state.files.directory);
    const clearFlashes = useStoreActions(actions => actions.flashes.clearFlashes);
    const setDirectory = ServerContext.useStoreActions(actions => actions.files.setDirectory);

    const username = useStoreState(state => state.user.data!.username);
    const sftp = ServerContext.useStoreState(state => state.server.data!.sftpDetails, isEqual);

    const setSelectedFiles = ServerContext.useStoreActions(actions => actions.files.setSelectedFiles);
    const selectedFilesLength = ServerContext.useStoreState(state => state.files.selectedFiles.length);

    const [ searchString, setSearchString ] = useState('');

    useEffect(() => {
        clearFlashes('files');
        setSelectedFiles([]);
        setDirectory(hashToPath(hash));
    }, [ hash ]);

    useEffect(() => {
        mutate();
    }, [ directory ]);

    const onSelectAllClick = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSelectedFiles(e.currentTarget.checked ? (files?.map(file => file.name) || []) : []);
    };

    if (error) {
        return (
            <ServerError message={httpErrorToHuman(error)} onRetry={() => mutate()} />
        );
    }

    const searchFiles = (event: ChangeEvent<HTMLInputElement>) => {
        if (files) {
            setSearchString(event.target.value);
            sortFiles(files, searchString);
            mutate();
        }
    };

    return (
        <ServerContentBlock title={'File Manager'} showFlashKey={'files'}>
            <input
                onChange={searchFiles}
                css={tw`rounded-lg bg-neutral-700 border-2 border-neutral-900 p-2 w-full mb-4`}
                placeholder={'Search for files...'}
            >
            </input>
            <div css={tw`flex flex-wrap-reverse md:flex-nowrap justify-center mb-4`}>
                <ErrorBoundary>
                    <FileManagerBreadcrumbs
                        css={tw`w-full`}
                        renderLeft={
                            <FileActionCheckbox
                                type={'checkbox'}
                                css={tw`mx-4`}
                                checked={selectedFilesLength === (files?.length === 0 ? -1 : files?.length)}
                                onChange={onSelectAllClick}
                            />
                        }
                    />
                </ErrorBoundary>
                <Can action={'file.create'}>
                    <ErrorBoundary>
                        <div css={tw`flex flex-shrink-0 flex-wrap-reverse md:flex-nowrap justify-end mb-4 md:mb-0 ml-0 md:ml-auto`}>
                            <NewDirectoryButton css={tw`w-full flex-none mt-4 sm:mt-0 sm:w-auto sm:mr-4`} />
                            <UploadButton css={tw`flex-1 mr-4 sm:flex-none sm:mt-0`} />
                            <NavLink
                                to={`/server/${id}/files/new${window.location.hash}`}
                                css={tw`flex-1 sm:flex-none sm:mt-0`}
                            >
                                <Button css={tw`w-full`}>
                                    New File
                                </Button>
                            </NavLink>
                        </div>
                    </ErrorBoundary>
                </Can>
            </div>
            {
                !files ?
                    <Spinner size={'large'} centered />
                    :
                    <>
                        {!files.length ?
                            <p css={tw`text-sm text-neutral-400 text-center`}>
                                This directory seems to be empty.
                            </p>
                            :
                            <CSSTransition classNames={'fade'} timeout={150} appear in>
                                <div>
                                    {files.length > 250 &&
                                        <div css={tw`rounded bg-yellow-400 mb-px p-3`}>
                                            <p css={tw`text-yellow-900 text-sm text-center`}>
                                                This directory is too large to display in the browser,
                                                limiting the output to the first 250 files.
                                            </p>
                                        </div>
                                    }
                                    {
                                        sortFiles(files.slice(0, 250), searchString).map(file => (
                                            <FileObjectRow key={file.key} file={file} />
                                        ))
                                    }
                                    <MassActionsBar />
                                </div>
                            </CSSTransition>
                        }
                    </>
            }
            <Can action={'file.sftp'}>
                <TitledGreyBox title={'SFTP Details'} css={tw`mt-6 md:mt-4`}>
                    <div>
                        <Label>Server Address</Label>
                        <CopyOnClick text={`sftp://${formatIp(sftp.ip)}:${sftp.port}`}>
                            <Input
                                type={'text'}
                                value={`sftp://${formatIp(sftp.ip)}:${sftp.port}`}
                                readOnly
                            />
                        </CopyOnClick>
                    </div>
                    <div css={tw`mt-6`}>
                        <Label>Username</Label>
                        <CopyOnClick text={`${username}.${id}`}>
                            <Input
                                type={'text'}
                                value={`${username}.${id}`}
                                readOnly
                            />
                        </CopyOnClick>
                    </div>
                </TitledGreyBox>
            </Can>
        </ServerContentBlock>
    );
};
