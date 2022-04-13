import React, { useState } from 'react';
import PageContentBlock from '@/components/elements/PageContentBlock';
import tw from 'twin.macro';
import ServerErrorSvg from '@/assets/images/server_error.svg';
import Button from '../elements/Button';
import renewServer from '@/api/store/renewServer';
import { ServerContext } from '@/state/server';
import useFlash from '@/plugins/useFlash';
import FlashMessageRender from '../FlashMessageRender';

const RenewalSuspended = () => {
    const { addFlash, clearFlashes, clearAndAddHttpError } = useFlash();
    const [ isSubmit, setSubmit ] = useState(false);

    const uuid = ServerContext.useStoreState(state => state.server.data!.uuid);
    const current = ServerContext.useStoreState(state => state.server.data!.renewal);

    const submit = () => {
        clearFlashes('server:renewal');
        setSubmit(true);

        renewServer(uuid, current)
            .then(() => setSubmit(false))
            .then(() => addFlash({
                type: 'success',
                key: 'server:renewal',
                message: 'Server has been renewed for an extra 7 days.',
            }))
            .catch(error => {
                console.error(error);
                clearAndAddHttpError({ key: 'server:renewal', error });
                setSubmit(false);
            });
    };

    return (
        <>
            <FlashMessageRender byKey={'server:renewal'} css={tw`mb-1`}/>
            <PageContentBlock>
                <div css={tw`flex justify-center`}>
                    <div css={tw`w-full sm:w-3/4 md:w-1/2 p-12 md:p-20 bg-neutral-100 rounded-lg shadow-lg text-center relative`}>
                        <img src={ServerErrorSvg} css={tw`w-2/3 h-auto select-none mx-auto`}/>
                        <h2 css={tw`mt-10 text-neutral-900 font-bold text-4xl`}>Suspended</h2>
                        <p css={tw`text-sm text-neutral-700 mt-2`}>
                            Your server has been suspended due to it not being renewed on time.
                            Please click the &apos;Renew&apos; button in order to reactivate
                            your server. If you do not have enough coins to do so, please
                            use the Nero App to earn them.
                        </p>
                        <Button
                            css={tw`mt-1`}
                            onClick={submit}
                            disabled={isSubmit}
                        >
                            Renew Now
                        </Button>
                    </div>
                </div>
            </PageContentBlock>
        </>
    );
};

export default RenewalSuspended;
